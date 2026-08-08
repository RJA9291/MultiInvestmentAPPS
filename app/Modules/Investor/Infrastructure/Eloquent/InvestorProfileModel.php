<?php

namespace App\Modules\Investor\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class InvestorProfileModel extends Model
{
    use HasUuids;

    protected $table = 'investor_profiles';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'investor_user_id',
        'investor_type',
        'verification_status',
        'company_name',
        'investment_range',
        'preferred_industry',
        'risk_appetite',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];
}
