<?php

namespace App\Modules\Document\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/** Eloquent model for `document_versions` (DB-007). Append-only — no updated_at. */
class DocumentVersionModel extends Model
{
    use HasUuids;

    protected $table = 'document_versions';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'document_id',
        'version_number',
        'file_id',
        'file_name',
        'mime_type',
        'storage_path',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
