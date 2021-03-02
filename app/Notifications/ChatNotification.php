<?php

namespace App\Notifications;

use Auth;
use App\Models\Chat\Chat;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChatNotification extends Notification
{
    use Queueable;
    public $chat;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Chat $chat)
    {
        $this->chat = $chat;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('The introduction to the notification.')
                    ->action('Notification Action', url('/'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
           $fullname = Auth::user()->first_name." ".Auth::user()->last_name;
            return [
                'user_id' =>  Auth::user()->id,
                'username' => $fullname,
                'avatar' => Auth::user()->avatar,
                'chat_id' => $this->chat->id,
            ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
                "data" => $this->toArray($notifiable),
                "dataType" => "ChatNotification",
            ]
        );
    }
}
