<?php

namespace App\Notifications;



use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskReassigned extends Notification
{
    use Queueable;

    protected Task $task;
    protected string $previousAssignee;
    protected string $newAssignee;
    protected string $reassignedBy;

    public function __construct(
        Task $task,
        string $previousAssignee,
        string $newAssignee,
        string $reassignedBy
    ) {
        $this->task = $task;
        $this->previousAssignee = $previousAssignee;
        $this->newAssignee = $newAssignee;
        $this->reassignedBy = $reassignedBy;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'task_reassigned',
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'previous_assignee' => $this->previousAssignee,
            'new_assignee' => $this->newAssignee,
            'reassigned_by' => $this->reassignedBy,
            'message' => $this->reassignedBy . ' reassigned a task to you.',
            'url' => route('tasks.show', $this->task->id),
            'reassigned_at' => now()->toDateTimeString(),
        ];
    }
}

