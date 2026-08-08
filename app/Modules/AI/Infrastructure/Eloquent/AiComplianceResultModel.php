<?php

namespace App\Modules\AI\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent model for `ai_compliance_results` (DB-044).
 * Deliberately NO SoftDeletes/hard-delete path in application code — every
 * run is kept for audit ("Old results -> keep for audit", Project Owner's
 * rule §5); only `status` flips ACTIVE -> SUPERSEDED, content never changes.
 */
class AiComplianceResultModel extends Model
{
    use HasUuids;

    protected $table = 'ai_compliance_results';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'project_id',
        'risk_score',
        'issues',
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
        'citations' => 'array',
        'ai_used' => 'boolean',
    ];
}
