<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskRoutingLog extends Model
{
    protected $table = 'task_routing_logs';

    public $timestamps = false;

    protected $fillable = [
        'task_routing_id',
        'user_id',
        'action',
        'previous_status',
        'new_status',
        'remarks',
        'details',
        'created_at',
    ];

    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime',
    ];

    public function routing(): BelongsTo
    {
        return $this->belongsTo(TaskRouting::class, 'task_routing_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
}