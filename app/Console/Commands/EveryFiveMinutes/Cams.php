<?php

namespace App\Console\Commands\EveryFiveMinutes;

use App\Models\Satellite\SatelliteAccount;
use App\Models\Statistics\Statistics;
use App\Models\Statistics\StatisticSummary;
use App\User;
use Carbon\Carbon;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Chrome\ChromeProcess;

class Cams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:cams';

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


    protected function cams()
    {
        $url = "https://models.streamray.com/";
        $dashboard = "https://models.streamray.com/p/cams/view.cgi?action=earnings";
        $username = "AlyssonCarterr";
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
            ->type('handle', $username)
            ->type('password', $password)
            ->press('.bttn');

        $browser->visit($dashboard);

        try {
            $text = $browser->waitFor("#models_earnings > div:nth-child(3) > div > div:nth-child(2) > div:nth-child(1) > table > tfoot > tr > td._total_cell.lineitems > div:nth-child(2) > span:nth-child(1)", 50)
                ->text('#models_earnings > div:nth-child(3) > div > div:nth-child(2) > div:nth-child(1) > table > tfoot > tr > td._total_cell.lineitems > div:nth-child(2) > span:nth-child(1)');
            $text = explode('$', $text);
            $text = $text[1];
            
            $browser->quit();
            $process->stop();

            try
            {
                DB::beginTransaction();
                if ($text !== '0.00'){
                    $week_start = Carbon::now()->startOfWeek(Carbon::SUNDAY)->toDateString();
                    $week_end = Carbon::now()->endOfWeek(Carbon::SATURDAY)->toDateString();
                    $range = $week_start." / ".$week_end;
                    $model_id = 6;

                    $satellite = SatelliteAccount::where('user_id', '=', $model_id)->first();
                    $user = User::where('id', '=', $model_id)->first();
                    $stats = Statistics::where([['user_id', '=', $model_id], ['range', '=', $range], ['setting_page_id', '=', 4]])->first();

                    $this->info("******************inserting stats");
                    if(!is_null($stats))
                    {
                        $stats->update([
                            'value' => $text
                        ]);
                    }
                    else{
                        Statistics::create([
                            'satellite_account_id' => $satellite->id,
                            'user_id' => $model_id,
                            'setting_page_id' => 2,
                            'setting_location_id' => $user->setting_location_id,
                            'value' => $text,
                            'range' => $range,
                            'date' => Carbon::now()->format('Y-m-d')
                        ]);
                    }

                    $summary = StatisticSummary::where([['user_id', '=', $model_id], ['range', '=', $range]])->first();
                    if (!is_null($summary))
                    {
                        $summary->update([
                            'value' => $summary->value + $text
                        ]);
                    }
                    else
                    {

                        $this->info("******************inserting summary");
                        StatisticSummary::create([
                            'user_id' => $model_id,
                            'value' => $text,
                            'goal' => 50.00,
                            'record' => 150.00,
                            'range' => $range,
                        ]);

                    }

                    DB::commit();
                }
            }
            catch (\Exception $ex)
            {
                DB::rollBack();
            }
        } catch (TimeOutException $e) {
            $browser->quit();
            $process->stop();
        }

        return $text;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("inicia cron job");
        $this->cams();
        $this->info("******************termina cron job");
    }
}
