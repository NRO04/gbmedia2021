<?php

namespace App\Notifications;

use App\Models\Tasks\TaskComment;
use App\Models\Tasks\Task;
use Auth;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskCommentNotification extends Notification
{
    use Queueable;

    public $task_comment;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(TaskComment $task_comment, Task $task)
    {
        $this->task = $task;
        $this->task_comment = $task_comment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database' , 'broadcast'];
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
        $comment = strip_tags($this->task_comment->comment);
        return [
            'username' => $fullname,
            'avatar' => Auth::user()->avatar,
            'title' => $this->task->title,
            'comment' => $comment,
            'task_id' => $this->task->id,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
                "data" => $this->toArray($notifiable),
                "dataType" => "TaskCommentNotification",
            ]
        );
    }
}
