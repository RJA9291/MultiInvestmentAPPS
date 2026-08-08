<?php

namespace App\Modules\Identity\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * DB-002: user_roles — the combinable RoleSet Value Object persisted as a
 * queryable, append-only-per-grant pivot (role history preserved for
 * UserRoleAssigned/EVT-002 auditing; revocation is `revoked_at` being set,
 * never a delete, per the locked "Soft Delete: No" note on DB-002).
 */
class UserRoleModel extends Model
{
    use HasUuids;

    public const UPDATED_AT = null;

    protected $table = 'user_roles';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'role',
        'granted_at',
        'granted_by',
        'revoked_at',
        'revoked_by',
    ];

    protected $casts = [
        'granted_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];
}
