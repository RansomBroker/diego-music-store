<?php

namespace App\Notifications;

use App\Models\EmployeeTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewEmployeeTaskNotification extends Notification
{
    use Queueable;

    public EmployeeTask $task;

    /**
     * Create a new notification instance.
     */
    public function __construct(EmployeeTask $task)
    {
        $this->task = $task;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'tugas',
            'title' => 'Tugas Baru: ' . $this->task->title,
            'message' => $this->task->description,
            'task_id' => $this->task->id,
        ];
    }
}
