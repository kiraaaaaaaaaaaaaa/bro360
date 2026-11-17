<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class TaskAttachment extends Model
{
    protected $table = 'task_attachments';

    public $timestamps = false;

    protected $fillable = [
        'task_id',
        'uploaded_by',
        'original_name',
        'file_path',
        'mime_type',
        'file_size',
        'google_drive_file_id',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'created_at' => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
    
}