<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskType extends Model
{
    protected $table = 'task_types';

    protected $fillable = [
        'title',
        'description',
        'category',
        'workflow_template_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function workflowTemplate(): BelongsTo
    {
        return $this->belongsTo(
            WorkflowTemplate::class,
            'workflow_template_id'
        );
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'task_type_id');
    }
}

