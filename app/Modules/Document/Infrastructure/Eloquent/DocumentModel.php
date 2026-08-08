<?php

namespace App\Modules\Document\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Eloquent model for `documents` (DB-006). SoftDeletes — WAJIB per BR-032. */
class DocumentModel extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'documents';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'project_id',
        'document_type',
        'file_id',
        'file_name',
        'mime_type',
        'storage_path',
        'is_approved',
        'uploaded_by',
        'uploaded_at',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'uploaded_at' => 'datetime',
        'approved_at' => 'datetime',
    ];
}
