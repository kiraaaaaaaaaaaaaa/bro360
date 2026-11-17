<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskRouting extends Model
{
    protected $table = 'task_routings';

    protected $fillable = [
        'task_id',
        'workflow_step_id',
        'destination_type',
        'destination_id',
        'review_order',
        'status',
        'qr_token',
        'dispatched_by',
        'dispatched_at',
        'receiving_name',
        'receiving_position',
        'receiving_office',
        'received_at',
        'receiving_photo_drive_id',
        'receiving_photo_name',
        'receiving_remarks',
        'remarks',
        'action_by',
        'action_at',
    ];

    protected $casts = [
        'destination_id' => 'integer',
        'review_order' => 'integer',
        'dispatched_at' => 'datetime',
        'received_at' => 'datetime',
        'action_at' => 'datetime',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function workflowStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class, 'workflow_step_id');
    }

    public function dispatcher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispatched_by');
    }

    public function actionBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'action_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(TaskRoutingLog::class, 'task_routing_id')
            ->orderBy('created_at');
    }
    public function destinationDivision(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'destination_id');
    }

    public function destinationOffice(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'destination_id');
    }
}
