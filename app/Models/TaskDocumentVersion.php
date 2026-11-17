<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskDocumentVersion extends Model
{
    protected $table = 'task_document_versions';

    protected $fillable = [
        'task_document_id',
        'version_number',
        'drive_file_id',
        'drive_folder_id',
        'google_docs_url',
        'original_name',
        'mime_type',
        'file_size',
        'status',
        'revision_remarks',
        'uploaded_by',
        'submitted_at',
        'approved_at',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'file_size' => 'integer',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    // Parent document
    public function document(): BelongsTo
    {
        return $this->belongsTo(
            TaskDocument::class,
            'task_document_id'
        );
    }

    // Person who uploaded this revision
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Check whether this is the current version
    public function isCurrent(): bool
    {
        return (int) $this->document?->current_version_id === $this->id;
    }

    // Check whether this is the approved version
    public function isApprovedVersion(): bool
    {
        return (int) $this->document?->approved_version_id === $this->id;
    }
}