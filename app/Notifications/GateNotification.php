<?php

namespace App\Notifications;

use App\BarrierGate;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class GateNotification extends Notification
{
    use Queueable;

    protected $gate;

    protected $message;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(BarrierGate $gate, $message)
    {
        $this->gate = $gate;
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'gate' => $this->gate->nama,
            'message' => $this->message
        ];
    }
}
