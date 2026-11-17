<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PapActivity extends Model
{
    use SoftDeletes;

    protected $table = 'pap_activities';

    protected $fillable = [
        'pap_id',
        'title',
        'description',
        'location',
        'start_date',
        'end_date',
        'quarter',
        'status',
        'focal_user_id',
        'created_by',
        'updated_by',
        'google_drive_folder_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function pap(): BelongsTo
    {
        return $this->belongsTo(Pap::class, 'pap_id');
    }

    public function focalPerson(): BelongsTo
    {
        return $this->belongsTo(User::class, 'focal_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'pap_activity_id');
    }
    public function getProgressAttribute(): float
{
    $tasks = $this->tasks()
        ->with('reviews')
        ->get();

    if ($tasks->isEmpty()) {
        return 0;
    }

    $taskProgresses = $tasks->map(function ($task) {
        $reviews = $task->reviews;

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

        return $reviewProgress->avg();
    });

    return round($taskProgresses->avg(), 2);
}
}

