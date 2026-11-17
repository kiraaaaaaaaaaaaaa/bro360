<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskStatus extends Model
{
    protected $table = 'task_statuses';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'color',
        'description',
        'is_terminal',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_terminal' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'status_id');
    }
}