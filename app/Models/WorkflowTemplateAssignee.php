<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowTemplateAssignee extends Model
{
    protected $table = 'workflow_template_assignees';

    protected $fillable = [
        'workflow_template_step_id',
        'assignment_type',
        'assignment_id',
        'review_order',
    ];

    protected $casts = [
        'assignment_id' => 'integer',
        'review_order' => 'integer',
    ];

    public function workflowStep(): BelongsTo
    {
        return $this->belongsTo(
            WorkflowStep::class,
            'workflow_template_step_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignment_id')
            ->where('assignment_type', 'user');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'assignment_id')
            ->where('assignment_type', 'role');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'assignment_id')
            ->where('assignment_type', 'position');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class, 'assignment_id')
            ->where('assignment_type', 'division');
    }

    public function organizationalUnit(): BelongsTo
    {
        return $this->belongsTo(
            OrganizationalUnit::class,
            'assignment_id'
        )->where('assignment_type', 'organizational_unit');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'assignment_id')
            ->where('assignment_type', 'office');
    }
}