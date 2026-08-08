<?php

namespace App\Modules\Compliance\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/** Eloquent model for `compliance_review_comments` (DB-034). Append-only — no updated_at. */
class ComplianceReviewCommentModel extends Model
{
    use HasUuids;

    protected $table = 'compliance_review_comments';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false; // created_at only, set via useCurrent() at the DB layer

    protected $fillable = [
        'compliance_review_id',
        'comment',
        'created_by',
    ];
}
