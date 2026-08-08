<?php

namespace App\Modules\AI\Domain\Exceptions;

use DomainException;

/**
 * Thrown by AiResponseValidator when a provider's raw response does not
 * match the locked API-012 contract shape or value ranges. This project's
 * "never assume, never invent" rule applies to malformed AI output too: a
 * response this exception rejects must never be silently coerced or passed
 * through to a Compliance Officer as if it were valid.
 */
class InvalidAiPrecheckResponseException extends DomainException
{
}
