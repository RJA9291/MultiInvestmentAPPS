<?php

namespace App\Modules\AI\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent model for `ai_document_verifications` (DB-045). Same permanent-
 * audit discipline as AiComplianceResultModel: no SoftDeletes/hard-delete
 * path in application code, only `status` flips ACTIVE -> SUPERSEDED.
 */
class AiDocumentVerificationResultModel extends Model
{
    use HasUuids;

    protected $table = 'ai_document_verifications';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'document_id',
        'completeness_score',
        'issues',
        'risk_flags',
        'recommendation',
        'confidence',
        'citations',
        'status',
        'ai_used',
        'prompt_code',
        'prompt_version',
        'model_code',
    ];

    protected $casts = [
        'issues' => 'array',
        'risk_flags' => 'array',
        'citations' => 'array',
        'ai_used' => 'boolean',
        'confidence' => 'float',
    ];
}
