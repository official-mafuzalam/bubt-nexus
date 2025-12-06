<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class NewAssignmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Assignment $assignment)
    {
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Assignment: ' . $this->assignment->title)
            ->line('A new assignment has been posted in ' . $this->assignment->class->name)
            ->line('Deadline: ' . $this->assignment->deadline->format('F j, Y g:i A'))
            ->action('View Assignment', route('assignments.show', [$this->assignment->class_id, $this->assignment->id]))
            ->line('Thank you for using our application!');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'assignment_id' => $this->assignment->id,
            'class_id' => $this->assignment->class_id,
            'title' => $this->assignment->title,
            'message' => 'New assignment posted: ' . $this->assignment->title,
            'url' => route('assignments.show', [$this->assignment->class_id, $this->assignment->id]),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'assignment_id' => $this->assignment->id,
            'class_id' => $this->assignment->class_id,
            'title' => $this->assignment->title,
            'message' => 'New assignment posted: ' . $this->assignment->title,
        ]);
    }
}