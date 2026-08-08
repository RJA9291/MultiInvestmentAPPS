<?php

namespace App\Modules\AI\Infrastructure\Registry\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Eloquent model for `prompts` (08_DATABASE_DESIGN.md v2.0.0 §15, PDL-050).
 * Deliberately NO SoftDeletes — registries deprecate via status='Deprecated'
 * only (PDL-037), never a delete.
 */
class PromptModel extends Model
{
    use HasUuids;

    protected $table = 'prompts';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $casts = [
        'dependencies' => 'array',
    ];
}
