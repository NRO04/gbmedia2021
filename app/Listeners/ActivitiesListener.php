<?php

namespace App\Listeners;

use App\Events\Activities;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ActivitiesListener
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
     * @param  Activities  $event
     * @return void
     */
    public function handle(Activities $event)
    {
        //
    }
}
