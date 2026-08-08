<?php

namespace App\Modules\Compliance\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent model for `compliance_reviews` (DB-033).
 * Deliberately NO SoftDeletes trait — PDL-028, permanent history, never deleted.
 */
class ComplianceReviewModel extends Model
{
    use HasUuids;

    protected $table = 'compliance_reviews';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'project_id',
        'cycle_number',
        'reviewer_user_id',
        'status',
        'opened_at',
        'decided_at',
        'decision_made_by', // PDL-059, was decided_by
        'decision_source', // PDL-059, 'HUMAN' | 'AI_ASSISTED', provenance only
    ];

    public function comments()
    {
        return $this->hasMany(ComplianceReviewCommentModel::class, 'compliance_review_id');
    }
}
