<?php

namespace App\Console\Commands\EveryFiveMinutes;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Console\Command;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Chrome\ChromeProcess;

class Firecams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:firecams';

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

    protected function firecams()
    {
        $url = "https://livecammates.com";
        $dashboard = "https://model.livecammates.com/stats/earnings";
        $username = "lfmm@yopmail.com";
        $password = "Molina89";
        $text = "";
        $host = 'http://localhost:9515';

        $process = (new ChromeProcess)->toProcess();
        if ($process->isStarted()) {
            $process->stop();
        }
        $process->start();

        $options      = (new ChromeOptions)->addArguments(['--disable-gpu', '--headless', '--no-sandbox']);
        $capabilities = DesiredCapabilities::chrome()
            ->setCapability(ChromeOptions::CAPABILITY, $options)
            ->setPlatform('Windows');
        $driver = retry(5, function () use ($capabilities, $host) {
            return RemoteWebDriver::create($host, $capabilities);
        }, 50);

        $browser = new Browser($driver);
        $browser->resize(1920, 1080);
        $browser->visit($url)
            ->press('#js-app > div > div > div.fullpage.fullpage-wrapper > section.section.section--introduction.fp-section.active > div.section__content > div.button-wrap > a:nth-child(2)')
            ->type('email', $username)
            ->type('password', $password)
            ->press("#js-app > div > div.popup-handler > div > div > div > div > div.block-content > form > div.submit-block.submit-block-sing > button");

        $browser->visit($dashboard);

        try {
            //            $nick = "AlyssonCarter";
            //            $text = $browser->waitFor("#".$nick."-earnings > div > table > tbody > tr.odd-row.daily-summaries > td:nth-child(2)", 50)
            //                ->text("#".$nick."-earnings > div > table > tbody > tr.odd-row.daily-summaries > td:nth-child(2)");

            /*$browser->quit();
            $process->stop();*/
        } catch (TimeOutException $e) {
            $browser->quit();
            $process->stop();
        }

        return response()->json($text);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("inicia cron job");
        $text = $this->firecams();
        $this->info('****' . $text . '****');
        $this->info("******************termina cron job");
    }
}
