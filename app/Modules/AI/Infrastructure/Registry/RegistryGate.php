<?php

namespace App\Modules\AI\Infrastructure\Registry;

use App\Modules\AI\Infrastructure\Registry\Eloquent\PromptModel;

/**
 * RegistryGate (10_PLATFORM_GOVERNANCE.md §1, PDL-041, PDL-050)
 *
 * The runtime enforcement point for "no registry entry may be used in
 * production unless Status = Active and an assigned Owner." This is what
 * turns PDL-041 from a documented rule into an actual behavior: any AI
 * capability Service MUST call this before running, never assume a prompt
 * or capability is ready just because a row exists.
 */
class RegistryGate
{
    /**
     * @return array{active: bool, prompt: ?PromptModel, reason: ?string}
     */
    public function checkPrompt(string $code): array
    {
        $prompt = PromptModel::where('code', $code)->first();

        if (! $prompt) {
            return ['active' => false, 'prompt' => null, 'reason' => "No registry entry found for {$code}."];
        }

        if ($prompt->status !== 'Active') {
            return [
                'active' => false,
                'prompt' => $prompt,
                'reason' => "{$code} has Status = {$prompt->status}, not Active (PDL-041).",
            ];
        }

        if (empty($prompt->owner)) {
            return [
                'active' => false,
                'prompt' => $prompt,
                'reason' => "{$code} has no assigned Owner (PDL-041).",
            ];
        }

        return ['active' => true, 'prompt' => $prompt, 'reason' => null];
    }
}
