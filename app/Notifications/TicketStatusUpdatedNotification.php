<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketStatusUpdatedNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    protected $ticket;

    /**
     * Create a new notification instance.
     */
    public function __construct($ticket)
    {
        $this->ticket = $ticket;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    // public function toMail(object $notifiable): MailMessage
    // {
    //     return (new MailMessage)
    //         ->line('The introduction to the notification.')
    //         ->action('Notification Action', url('/'))
    //         ->line('Thank you for using our application!');
    // }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $statusLabel = match ($this->ticket->status) {
            'in_progress' => 'sedang diproses',
            'resolved' => 'telah selesai',
            'closed' => 'ditutup',
            default => $this->ticket->status
        };

        $message = "Komplain Anda terkait '{$this->ticket->title}' saat ini {$statusLabel}.";

        if (!empty($this->ticket->admin_note)) {
            $message .= " Catatan: " . $this->ticket->admin_note;
        }

        return [
            'type' => 'ticket_updated',
            'title' => 'Update Komplain: ' . $this->ticket->title,
            'message' => $message,
            'action_url' => '/tenants/tickets/' . $this->ticket->id
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
