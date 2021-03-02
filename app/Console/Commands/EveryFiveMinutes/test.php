<?php

namespace App\Console\Commands\EveryFiveMinutes;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Console\Command;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Chrome\ChromeProcess;

class test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Iniciando Cron de prueba');
        $this->autoloadTest();
        $this->info('Cron finalizado ');
    }

    public function autoloadTest()
    {

        $url = "https://www.streamatemodels.com/smm/login.php"; //Url de pagina.
        $username = "NAACC@YOPMAIL.COM";
        $password = "Cuenca1999";
        $host = 'http://localhost:9515';


        $process = (new ChromeProcess)->toProcess();
        ($process->isStarted()) ? $process->stop() : null; //Verifica si hay algun proceso de chrome ejecuntandose y lo cierra.

        $process->start(); // Inicia el nuevo proceso de Chrome.
        /*
        --disable-gpu : deshabilita los graficos.

        */
        // $options = (new ChromeOptions)->addArguments(['--disable-gpu', '--headless', '--no-sandbox']);
        $capabilities = DesiredCapabilities::chrome(); // Se vinculan las capacidades deseadas.
        $driver = retry(5, function () use ($capabilities, $host) {
            return RemoteWebDriver::create($host, $capabilities); //Crea
        }, 50);

        $browser = new Browser($driver);
        $browser->resize(1920, 1080); //Establece la resolucion del navegador.
        $visit = $browser->visit($url); //Visita (Ingresa) en la url.
        $visit->type('sausr', $username)->type('sapwd', $password); //Escribe en los campos que se especifican.
        $visit->press('#login-form-submit'); // Presiona un boton.
        $browser->visit('https://www.streamatemodels.com/smm/reports/earnings/EarningsReportPivot.php');

        $value = $browser->waitFor('.earnings', 2)->text('.earnings'); //Espera y obtiene el texto que esta dentro de la clase.
        $value = explode('$', $value); //Transforma el texto a un array.
        $this->info("Value " . $value[1]);



        // $browser->quit();
        // $process->stop();
    }
}
