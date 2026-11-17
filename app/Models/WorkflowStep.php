<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowStep extends Model
{
    protected $table = 'workflow_steps';

    protected $fillable = [
        'workflow_template_id',
        'role_id',
        'step_order',
        'step_name',
        'assignment_type',
        'assignment_id',
        'action_type',
        'completion_rule',
        'due_days',
        'allow_return',
        'allow_reject',
        'allow_not_applicable',
        'is_required',
    ];

    protected $casts = [
        'step_order' => 'integer',
        'due_days' => 'integer',
        'assignment_id' => 'integer',
        'role_id' => 'integer',
        'allow_return' => 'boolean',
        'allow_reject' => 'boolean',
        'allow_not_applicable' => 'boolean',
        'is_required' => 'boolean',
    ];

    public function workflowTemplate(): BelongsTo
    {
        return $this->belongsTo(
            WorkflowTemplate::class,
            'workflow_template_id'
        );
    }

    public function assignees(): HasMany
    {
        return $this->hasMany(
            WorkflowTemplateAssignee::class,
            'workflow_template_step_id'
        )->orderBy('review_order');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}