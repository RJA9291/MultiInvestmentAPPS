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

    /**
     * DB-002's user_roles has no created_at/updated_at columns at all —
     * only granted_at/revoked_at (see migration). `UPDATED_AT = null` alone
     * still leaves Eloquent's default `$timestamps = true` trying to insert
     * created_at, which doesn't exist in the schema (caused a real
     * "column created_at does not exist" failure in production). Disabling
     * both via $timestamps = false is the correct fix, not just nulling one.
     */
    public $timestamps = false;

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
