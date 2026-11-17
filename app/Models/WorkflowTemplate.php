<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowTemplate extends Model
{
    protected $table = 'workflow_templates';

    protected $fillable = [
        'name',
        'module',
        'process_type',
        'description',
        'version',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'version' => 'string',
        'is_active' => 'boolean',
    ];

    public function steps(): HasMany
    {
        return $this->hasMany(
            WorkflowStep::class,
            'workflow_template_id'
        )->orderBy('step_order');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function taskTypes(): HasMany
{
    return $this->hasMany(TaskType::class, 'workflow_template_id');
}
}