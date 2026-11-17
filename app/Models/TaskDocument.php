<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskDocument extends Model
{
   use SoftDeletes;

    protected $table = 'task_documents';

    protected $fillable = [
        'task_id',
        'document_name',
        'description',
        'current_version_id',
        'approved_version_id',
        'created_by',
        'updated_by',
    ];

    // Parent task
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    // All revisions of this document
    public function versions(): HasMany
    {
        return $this->hasMany(
            TaskDocumentVersion::class,
            'task_document_id'
        )->orderByDesc('version_number');
    }

    // Latest working version
    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(
            TaskDocumentVersion::class,
            'current_version_id'
        );
    }

    // Latest approved version
    public function approvedVersion(): BelongsTo
    {
        return $this->belongsTo(
            TaskDocumentVersion::class,
            'approved_version_id'
        );
    }

    // Original uploader
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Last person who updated the document record
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
