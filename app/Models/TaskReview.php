<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskReview extends Model
{
    protected $table = 'task_reviews';

    protected $fillable = [
        'task_id',
        'reviewer_id',
        'workflow_step_id',
        'review_order',
        'status',
        'remarks',
        'received_at',
        'reviewed_at',
        'returned_at',
    ];

    protected $casts = [
        'review_order' => 'integer',
        'received_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'returned_at' => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function workflowStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class, 'workflow_step_id');
    }
}