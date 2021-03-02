<?php

namespace App\Mail\Satellite;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ActivatedAccount extends Mailable
{
    use Queueable, SerializesModels;

    public $mail;
    public $subject;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mail)
    {
        $this->mail = $mail;
        $this->subject = "Su cuenta de " . $mail['pagina'] . " ha sido activada";
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('adminModules.satellite.emails.account.created');
    }
}
