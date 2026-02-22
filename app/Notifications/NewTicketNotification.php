<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTicketNotification extends Notification implements ShouldBroadcast
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
        return [
            'type' => 'new_ticket',
            'title' => 'Komplain Baru: ' . $this->ticket->title,
            'message' => 'Terdapat laporan komplain baru dari Kamar ' . ($this->ticket->room->room_number ?? 'Unknown'),
            'ticket_id' => $this->ticket->id,
            'action_url' => '/admin/tickets/' . $this->ticket->id
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            // Data yang akan langsung meluncur ke React frontend Anda
            'type' => 'new_ticket',
            'title' => 'Komplain Baru: ' . $this->ticket->title,
            'message' => 'Terdapat laporan komplain baru dari Kamar ' . ($this->ticket->room->room_number ?? 'Unknown'),
            'action_url' => '/admin/tickets/' . $this->ticket->id
        ]);
    }
}
