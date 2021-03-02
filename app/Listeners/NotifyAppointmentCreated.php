<?php

namespace App\Listeners;

use App\Events\AppointmentCreated;
use App\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class NotifyAppointmentCreated
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  AppointmentCreated  $event
     * @return void
     */
    public function handle(AppointmentCreated $event)
    {
        // Access the post using $event->post...
       /* $user_id = 10;
        $users = User::where('setting_role_id', $user_id);

        foreach($users as $user) {
            Mail::to($user)->send('emails.post.created', $event->post);
        }*/

        $event->appointment->id;
    }
}
