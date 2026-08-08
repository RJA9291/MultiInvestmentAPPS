<?php

namespace App\Modules\AI\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class InvestorProjectMatchModel extends Model
{
    use HasUuids;

    protected $table = 'ai_investor_project_matches';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'investor_profile_id',
        'project_id',
        'score',
        'breakdown',
        'reasons',
        'confidence',
    ];

    protected $casts = [
        'score' => 'integer',
        'breakdown' => 'array',
        'reasons' => 'array',
        'confidence' => 'float',
    ];
}
