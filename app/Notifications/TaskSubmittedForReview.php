<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskSubmittedForReview extends Notification
{
    use Queueable;

    protected Task $task;
    protected string $senderName;

    public function __construct(Task $task, string $senderName)
    {
        $this->task = $task;
        $this->senderName = $senderName;
    }

    public function via(object $notifiable): array
    {
        
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
{
   
    return [
        'type' => 'task_submitted_for_review',
        'task_id' => $this->task->id,
        'task_title' => $this->task->title,
        'sender_name' => $this->senderName,
        'message' => $this->senderName . ' forwarded a document to you for review.',
        'url' => route('tasks.show', $this->task->id),
        'submitted_at' => now()->toDateTimeString(),
    ];
}
}