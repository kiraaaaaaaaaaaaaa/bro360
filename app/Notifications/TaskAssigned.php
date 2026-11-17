<?php

namespace App\Notifications;


use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssigned extends Notification
{
    use Queueable;

    protected Task $task;
    protected string $assignerName;

    public function __construct(Task $task, string $assignerName)
    {
        $this->task = $task;
        $this->assignerName = $assignerName;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'task_assigned',
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'sender_name' => $this->assignerName,
            'message' => $this->assignerName . ' assigned a task to you.',
            'url' => route('tasks.show', $this->task->id),
            'assigned_at' => now()->toDateTimeString(),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Task Assigned - BRO360')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line($this->assignerName . ' assigned a task to you.')
            ->line('Task: ' . $this->task->title)
            ->action('View Task', route('tasks.show', $this->task->id))
            ->line('Please log in to BRO360 to review your assigned task.');
    }
}

