<?php

namespace App\Modules\Project\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Eloquent persistence model for `projects` (DB-004). Deliberately kept out
 * of Domain/ — the Domain Entity (Domain/Entities/Project.php) never imports
 * this class directly; only Infrastructure/Mappers/ProjectMapper.php and
 * Infrastructure/Repositories/EloquentProjectRepository.php may.
 */
class ProjectModel extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'projects';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'project_code',
        'owner_user_id',
        'title',
        'description',
        'category',
        'status',
        'current_compliance_review_id',
    ];
}
