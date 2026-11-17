<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskReturnedToAssignee extends Notification
{
    use Queueable;

    protected Task $task;
    protected string $reviewerName;
    protected string $remarks;

    public function __construct(
        Task $task,
        string $reviewerName,
        string $remarks
    ) {
        $this->task = $task;
        $this->reviewerName = $reviewerName;
        $this->remarks = $remarks;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'task_returned',
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'reviewer_name' => $this->reviewerName,
            'message' => $this->reviewerName . ' returned a task for revision.',
            'remarks' => $this->remarks,
            'url' => route('tasks.show', $this->task->id),
            'returned_at' => now()->toDateTimeString(),
        ];
    }
}