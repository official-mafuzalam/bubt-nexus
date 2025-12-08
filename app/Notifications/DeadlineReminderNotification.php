<?php
// app/Notifications/DeadlineReminderNotification.php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class DeadlineReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Assignment $assignment)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail']; // Remove 'broadcast' if not using WebSockets
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $deadline = $this->assignment->deadline->format('F j, Y g:i A');
        $hoursLeft = now()->diffInHours($this->assignment->deadline);

        return (new MailMessage)
            ->subject('⏰ Assignment Deadline Reminder: ' . $this->assignment->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('This is a reminder about an upcoming assignment deadline.')
            ->line('**Assignment:** ' . $this->assignment->title)
            ->line('**Class:** ' . $this->assignment->class->name)
            ->line('**Deadline:** ' . $deadline)
            ->line('**Time Left:** ' . $hoursLeft . ' hours')
            ->action('View Assignment', route('assignments.show', [
                $this->assignment->class_id,
                $this->assignment->id
            ]))
            ->line('Please make sure to submit your assignment before the deadline!')
            ->salutation('Best regards,<br>' . config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'assignment_id' => $this->assignment->id,
            'class_id' => $this->assignment->class_id,
            'title' => $this->assignment->title,
            'message' => 'Assignment "' . $this->assignment->title . '" is due soon!',
            'deadline' => $this->assignment->deadline,
            'url' => route('assignments.show', [$this->assignment->class_id, $this->assignment->id]),
            'type' => 'deadline_reminder',
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'assignment_id' => $this->assignment->id,
            'class_id' => $this->assignment->class_id,
            'title' => $this->assignment->title,
            'message' => 'Assignment deadline reminder: ' . $this->assignment->title,
            'deadline' => $this->assignment->deadline->toIso8601String(),
        ]);
    }
}