<?php

namespace App\Modules\Identity\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** DB-001: users. Roles live in UserRoleModel (DB-002), not here. */
class UserModel extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'users';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'display_name',
        'email',
        'password_hash',
        'mfa_enabled',
        'is_suspended',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'mfa_enabled' => 'boolean',
        'is_suspended' => 'boolean',
    ];
}
