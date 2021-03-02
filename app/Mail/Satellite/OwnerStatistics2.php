<?php

namespace App\Mail\Satellite;

use App\Models\Satellite\SatelliteTemplateStatistic;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OwnerStatistics2 extends Mailable
{
    use Queueable, SerializesModels;
    public $mail;
    public $id;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $mail = SatelliteTemplateStatistic::first();
        if(file_exists( "../storage/app/public/GB/logo/logo.png" )){
            $mail["logo"] = "<img src='http://laravel.gbmediagroup.com/storage/app/public/GB/logo/logo.png' style='height: 40px'>";
        }
        else{
            $mail["logo"] = "<h1 style='color: #2553FF'>Grupo Bedoya</h1>";
        }

        $this->mail =  $mail;
        $this->id = $id;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $path = 'app/statistics/'.$this->id;
        return $this->markdown('emails.satellite.statistics')->subject('Estadisticas')->attach(storage_path($path.'/Pago.xlsx'));
    }
}
