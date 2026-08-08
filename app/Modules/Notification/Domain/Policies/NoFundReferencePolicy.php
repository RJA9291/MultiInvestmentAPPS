<?php

namespace App\Modules\Notification\Domain\Policies;

/**
 * NoFundReferencePolicy (BR-097, 06_DOMAIN_MODEL.md §8) — "notification
 * content columns must never reference fund/payment terms." The Project
 * Owner's Security Rules (§10, "JANGAN expose sensitive data dalam
 * message") name the same concern in different words; this Policy is the
 * single choke point `SendNotificationService` calls before ANY
 * notification is persisted, regardless of which Module/event triggered it.
 *
 * FLAGGED: a real implementation would need a maintained, versioned
 * blocklist (legal/compliance input, per this rule's Assumptions note in
 * `04_BUSINESS_RULES.md`) — this is a minimal, honest keyword-based
 * stand-in, not a claim of NLP-grade content moderation.
 */
class NoFundReferencePolicy
{
    private const BLOCKED_TERMS = [
        'wire transfer', 'bank account', 'routing number', 'account number',
        'investment amount', 'payment due', 'transfer funds', 'iban', 'swift code',
    ];

    public function isCompliant(string $title, string $message): bool
    {
        $haystack = mb_strtolower($title . ' ' . $message);

        foreach (self::BLOCKED_TERMS as $term) {
            if (str_contains($haystack, $term)) {
                return false;
            }
        }

        return true;
    }
}
