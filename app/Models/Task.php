<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    protected $table = 'tasks';

    protected $fillable = [
        'pap_id',
        'pap_activity_id',
        'pap_activity_task_id',
        'title',
        'description',
        'assigned_to',
        'created_by',
        'status_id',
        'priority',
        'start_date',
        'due_date',
        'completed_at',
        'external_link',
        'qr_review_token',
        'workflow_template_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function workflowTemplate()
{
    return $this->belongsTo(WorkflowTemplate::class, 'workflow_template_id');
}

    // Parent PAP
    public function pap(): BelongsTo
    {
        return $this->belongsTo(Pap::class, 'pap_id');
    }

    // Parent activity
    public function activity(): BelongsTo
    {
        return $this->belongsTo(PapActivity::class, 'pap_activity_id');
    }

    // Task category/template
    public function activityTask(): BelongsTo
    {
        return $this->belongsTo(PapActivityTask::class, 'pap_activity_task_id');
    }

    // Primary assignee
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Creator
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Task status
    public function status(): BelongsTo
    {
        return $this->belongsTo(TaskStatus::class, 'status_id');
    }

    // Multiple task assignments
    public function assignments(): HasMany
    {
        return $this->hasMany(TaskAssignment::class, 'task_id');
    }

    // Reviewer workflow
    public function reviews(): HasMany
    {
        return $this->hasMany(TaskReview::class, 'task_id')
            ->orderBy('review_order');
    }

    // Google Drive documents and revisions
    public function documents(): HasMany
    {
        return $this->hasMany(TaskDocument::class, 'task_id');
    }

    // Task comments
    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class, 'task_id');
    }

    // Task attachments
    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class, 'task_id');
    }

    // Activity logs
    public function activityLogs(): HasMany
    {
        return $this->hasMany(TaskActivityLog::class, 'task_id');
    }
    
    public function routings(): HasMany
    {
        return $this->hasMany(TaskRouting::class, 'task_id')
            ->orderBy('review_order');
    }

    public function taskType(): BelongsTo
    {
        return $this->belongsTo(TaskType::class, 'task_type_id');
    }

    public function getProgressAttribute(): float
{
    $reviews = $this->reviews;

    if ($reviews->isEmpty()) {
        return 0;
    }

    $reviewProgress = $reviews->map(function ($review) {
        return match ($review->status) {
            'Completed', 'Not Applicable' => 100,
            'Received' => 50,
            default => 0,
        };
    });

    return round($reviewProgress->avg(), 2);
}
}
