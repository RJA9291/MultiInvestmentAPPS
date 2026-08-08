<?php

namespace App\Modules\AI;

use App\Modules\AI\Application\Contracts\AiProviderGatewayInterface;
use App\Modules\AI\Application\Contracts\DocumentContentExtractorInterface;
use App\Modules\AI\Application\Listeners\HandleAiDocumentVerificationCompleted;
use App\Modules\AI\Application\Listeners\HandleAiPrecheckCompleted;
use App\Modules\AI\Application\Listeners\RunAiDocumentVerificationListener;
use App\Modules\AI\Application\Listeners\RunAiPrecheckListener;
use App\Modules\AI\Application\Listeners\TriggerAiDocumentVerification;
use App\Modules\AI\Application\Listeners\TriggerAiPrecheckListener;
use App\Modules\AI\Application\Listeners\TriggerMatchingOnInvestorVerified;
use App\Modules\AI\Application\Listeners\TriggerMatchingOnProjectPublished;
use App\Modules\AI\Domain\Events\AiCompliancePrecheckCompleted;
use App\Modules\AI\Domain\Events\AiCompliancePrecheckRequested;
use App\Modules\AI\Domain\Events\AiDocumentVerificationCompleted;
use App\Modules\AI\Domain\Events\AiDocumentVerificationRequested;
use App\Modules\AI\Domain\Repositories\AiComplianceResultRepositoryInterface;
use App\Modules\AI\Domain\Repositories\AiDocumentVerificationResultRepositoryInterface;
use App\Modules\AI\Domain\Repositories\InvestorProjectMatchRepositoryInterface;
use App\Modules\AI\Infrastructure\Extraction\PlainTextPassthroughExtractor;
use App\Modules\AI\Infrastructure\Gateway\NullAiProviderGateway;
use App\Modules\AI\Infrastructure\Gateway\OpenAiProviderGateway;
use App\Modules\AI\Infrastructure\Repositories\EloquentAiComplianceResultRepository;
use App\Modules\AI\Infrastructure\Repositories\EloquentAiDocumentVerificationResultRepository;
use App\Modules\AI\Infrastructure\Repositories\EloquentInvestorProjectMatchRepository;
use App\Modules\Document\Domain\Events\DocumentUploaded;
use App\Modules\Investor\Domain\Events\InvestorVerified;
use App\Modules\Project\Domain\Events\ProjectPublished;
use App\Modules\Project\Domain\Events\ProjectResubmitted;
use App\Modules\Project\Domain\Events\ProjectSubmitted;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * AI Module's own Service Provider (14_LARAVEL_BLUEPRINT.md §6).
 *
 * AiProviderGatewayInterface binds to OpenAiProviderGateway when
 * `OPENAI_API_KEY` is configured, else falls back to NullAiProviderGateway.
 * This binding was the ONLY place that needed to change to add a real
 * provider — exactly as ComplianceAssistantService's/AiDocumentVerification
 * Service's own doc comments anticipated.
 *
 * STATUS (2026-08-08, PDL-062): PROMPT-003/PROMPT-004 are now `Active` in
 * the Prompt Registry (`database/seeders/RegistrySeeder.php`,
 * `09_AI_ARCHITECTURE.md` §21), so RegistryGate WILL call this binding for
 * both Compliance pre-check and Document verification once `OPENAI_API_KEY`
 * is set. This activation was an explicit, logged Project Owner exception
 * (PDL-062) to skip the §18 Evaluation Framework for MVP launch speed — it
 * is NOT a claim that either prompt passed Grounding Accuracy/Citation
 * Accuracy/Hallucination Detection/Golden Questions. See each Prompt's
 * `evaluation` field in RegistrySeeder.php and PDL-062 in
 * `00_MASTER_PROMPT.md` for the exact authorization record.
 *
 * Event registration is intentionally kept HERE (this Module's own
 * ServiceProvider), not in a single central app/Providers/EventServiceProvider
 * with one big $listen array — that would recreate exactly the kind of
 * shared/global coupling 14_LARAVEL_BLUEPRINT.md's per-Module structure
 * (PDL-048) otherwise avoids. Compliance Module's own events/listeners stay
 * registered in ComplianceServiceProvider, unaffected by this file.
 *
 * WAJIB: register this provider in bootstrap/providers.php / config/app.php.
 */
class AIServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AiProviderGatewayInterface::class, function () {
            return empty(config('services.openai.api_key'))
                ? new NullAiProviderGateway()
                : new OpenAiProviderGateway();
        });
        $this->app->bind(AiComplianceResultRepositoryInterface::class, EloquentAiComplianceResultRepository::class);
        $this->app->bind(AiDocumentVerificationResultRepositoryInterface::class, EloquentAiDocumentVerificationResultRepository::class);
        $this->app->bind(DocumentContentExtractorInterface::class, PlainTextPassthroughExtractor::class);
        $this->app->bind(InvestorProjectMatchRepositoryInterface::class, EloquentInvestorProjectMatchRepository::class);
    }

    public function boot(): void
    {
        // Full chain (Project Owner's "AI Pre-check -> Event Flow" brief):
        // ProjectSubmitted/ProjectResubmitted
        //   -> TriggerAiPrecheckListener -> AiCompliancePrecheckRequested
        //   -> RunAiPrecheckListener -> dispatch(RunAiComplianceJob)
        //   -> [queued] RunAiComplianceJob -> AiCompliancePrecheckCompleted
        //   -> HandleAiPrecheckCompleted (reactive side effects only)
        Event::listen(ProjectSubmitted::class, TriggerAiPrecheckListener::class);
        Event::listen(ProjectResubmitted::class, TriggerAiPrecheckListener::class);
        Event::listen(AiCompliancePrecheckRequested::class, RunAiPrecheckListener::class);
        Event::listen(AiCompliancePrecheckCompleted::class, HandleAiPrecheckCompleted::class);

        // Full "AI Document Verification Module" chain (supersedes the
        // v3.22.0 single-listener DocumentAnalysisService hook — see
        // RunDocumentAnalysisOnUpload's doc comment, now deliberately NOT
        // registered below):
        // DocumentUploaded
        //   -> TriggerAiDocumentVerification -> AiDocumentVerificationRequested
        //   -> RunAiDocumentVerificationListener -> dispatch(RunAiDocumentVerificationJob)
        //   -> [queued] RunAiDocumentVerificationJob -> AiDocumentVerificationCompleted
        //   -> HandleAiDocumentVerificationCompleted (reactive: DocumentAiReviewReady + high-risk log)
        // Cross-Module reaction (PDL-020): AI Module listens to Document
        // Module's event; Document Module has no dependency back on AI Module.
        Event::listen(DocumentUploaded::class, TriggerAiDocumentVerification::class);
        Event::listen(AiDocumentVerificationRequested::class, RunAiDocumentVerificationListener::class);
        Event::listen(AiDocumentVerificationCompleted::class, HandleAiDocumentVerificationCompleted::class);

        // "AI Investor-Project Matching Engine" brief §6: only the two
        // triggers that correspond to a real, already-existing event are
        // wired — "InvestorProfileUpdated" does not exist (no profile-edit
        // capability built yet in the Investor Module) and is deliberately
        // left unwired, per this Module's own "never fabricate an event"
        // discipline (same as the Notification Module's scope decision).
        Event::listen(InvestorVerified::class, TriggerMatchingOnInvestorVerified::class);
        Event::listen(ProjectPublished::class, TriggerMatchingOnProjectPublished::class);
    }
}
