<?php

namespace App\Console\Commands\EveryFiveMinutes;

use App\Models\Attendance\Attendance;
use App\Models\monitoring\Monitoring;
use App\Models\Satellite\SatelliteAccount;
use App\Models\Settings\SettingPage;
use App\Models\Statistics\Commission;
use App\Models\Statistics\Statistics;
use App\Models\Statistics\StatisticSummary;
use App\User;
use Carbon\Carbon;
use DebugBar\DataCollector\DataCollector;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Exception\TimeoutException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Chrome\ChromeProcess;

class Streamate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:streamate';

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

   /*protected function storeCommission($model_id, $range, $total, $current_date, $location_id)
    {
        $exists = Attendance::where([
            ['model_id', $model_id],
            ['date', $current_date],
        ])->first();

        $summary = StatisticSummary::where([
            ['user_id', $model_id],
            ['range', $range],
        ])->first();

        if ($total > $summary->goal && !is_null($exists))
        {
            $commission_type = "meta_1";
            $this->insertCommission($model_id, $commission_type, $total, $current_date, $location_id);
            $this->increaseGoal($model_id, $current_date, $total, $summary->goal);
        }
        else
        {
            $commission_type = "meta_1";
            $this->removeCommission($model_id, $commission_type, $current_date, $location_id);
            $this->removeIncreaseGoal($model_id, $current_date);
            if ($total <= 0)
            {
               // remove report here
                $this->removeReport($model_id, $current_date);
            }
        }
    }

    protected function  insertCommission($model_id, $commission_type, $total, $date, $location_id)
    {
         $hasMonitors = $this->checkIfLocationHasActiveMonitors($location_id);
         $commission = "";
         
         if ($hasMonitors)
         {
            $commission_exists = Commission::where([
                ['model_id', $model_id],
                ['commission_type', $commission_type],
                ['date', $date],
                ['setting_location_id', $location_id],
            ])->exists();
            
            if (!$commission_exists)
            {
                // create commission here
                $commission = Commission::create([
                   'user_id' => $model_id,
                   'setting_location_id' => $location_id,
                   'commision_type' => $commission_type,
                   'total' => $total,
                   'date' => $date,
                ]);
            }
         }

         return $commission;
    }

    protected function removeCommission($model_id, $commission_type, $date, $location_id)
    {
        $commission_exists = Commission::where([
            ['model_id', $model_id],
            ['commission_type', $commission_type],
            ['date', $date],
            ['setting_location_id', $location_id],
        ])->first();
        
        if (!is_null($commission_exists))
        {
            // delete comission here
            $commission_exists->delete();
        }
    }

    protected function increaseGoal($model_id, $date, $value, $goal)
    {
        $increase_goal_exists = Commission::where([
            ['model_id'],
            [],
        ]);
        if ($increase_goal_exists)
        {
            // update goal in summary

        }
        else
        {
           // insert goal increase in summary
        }
    }

    protected function removeIncreaseGoal($model_id, $date)
    {
        $commission = Commission::where([
            ['model_id', $model_id],
            ['date', $date]
        ])->first();

        $commission->delete();
    }

    protected function checkIfLocationHasActiveMonitors($location_id)
    {
       $exists = User::where([
           ['setting_role_id', '=', 6],
           ['status', '!=', 1],
           ['setting_location_id', $location_id]
       ])->orWhere([
           ['setting_role_id', '=', 6],
           ['status', '=', 1],
           ['setting_location_id', 2]
       ])->exists();

       return $exists;
    }

    protected function storeReport($model_id, $monitor_id = NULL, $assigned_by = NULL, $range, $date, $status = 0, $location_id)
    {
        $report_exists = Monitoring::where('model_id', $model_id)
            ->where('date', $date)
            ->where('setting_location_id', $location_id)->exists();

        if (!$report_exists)
        {
            Monitoring::create([
                'model_id' => $model_id,
                'monitor_id' => $monitor_id,
                'assigned_by' => $assigned_by,
                'range' => $range,
                'date' => $date,
                'status' => $status,
                'setting_location_id' => $location_id
            ]);
        }
    }

    protected function removeReport($model_id, $date)
    {
           $report = Monitoring::where([
               ['model_id', $model_id],
               ['date', $date]
           ])->first();

           $report->delete();
    }*/

    protected function streamate()
    {
        $url = "https://www.streamatemodels.com/smm/login.php";
        $dashboard = "https://www.streamatemodels.com/smm/reports/earnings/EarningsReportPivot.php";
        $username = "LFMM1@YOPMAIL.COM";
        $password = "Molina89";
        $text = "";
        $host = 'http://localhost:9515';
        $model_nick = "AlyssonCarter";
        /*$pages = SatelliteAccount::select('setting_pages.name', 'setting_pages.id')
            ->join('setting_pages', 'setting_pages.id', '=', 'satellite_accounts.page_id')
            ->where('satellite_accounts.from_gb', '=', 1)
            ->where('satellite_accounts.status_id', '=', 2)->get();
        dd($pages);*/

        $process = (new ChromeProcess)->toProcess();
        if ($process->isStarted()) {
            $process->stop();
        }
        $process->start();

        $options = (new ChromeOptions)->addArguments(['--disable-gpu', '--headless', '--no-sandbox']);
        $capabilities = DesiredCapabilities::chrome() ;
//            ->setCapability(ChromeOptions::CAPABILITY, $options)
//            ->setPlatform('Windows');
        $driver = retry(5, function () use ($capabilities, $host) {
            return RemoteWebDriver::create($host, $capabilities);
        }, 50);

        $browser = new Browser($driver);
        $browser->resize(1920, 1080);
        $browser->visit($url)
            ->type('sausr', $username)
            ->type('sapwd', $password)
            ->press('login-form-submit');

        $browser->visit($dashboard)
            ->select('range', 'day')
            ->value('#earnday', Carbon::today()->format("M d, Y"))
            ->press('#getData');

        try {
            $nick = $model_nick;
            $text = $browser->waitFor("#".$nick."-earnings > div > table > tbody > tr.odd-row.daily-summaries > td:nth-child(2)", 50)
                ->text("#".$nick."-earnings > div > table > tbody > tr.odd-row.daily-summaries > td:nth-child(2)");

            $text = explode('$', $text);
            $text = $text[1];

            $this->info('**** Got =>'.$text.'****');

            $browser->quit();
            $process->stop();

            try
            {
                DB::beginTransaction();
                if ($text !== '0.00')
                {
                    $week_start = Carbon::now()->startOfWeek(Carbon::SUNDAY)->toDateString();
                    $week_end = Carbon::now()->endOfWeek(Carbon::SATURDAY)->toDateString();
                    $range = $week_start." / ".$week_end;
                    $model_id = 6;

                    $satellite = SatelliteAccount::where('user_id', '=', $model_id)->first();
                    $user = User::where('id', '=', $model_id)->first();
                    $stats = Statistics::where([['user_id', '=', $model_id], ['range', '=', $range], ['setting_page_id', '=', 2]])->first();

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
                        StatisticSummary::updateOrCreate([
                            'user_id' => $model_id,
                            'value' => $text,
                            'goal' => 50.00,
                            'record' => 150.00,
                            'range' => $range,
                        ]);

                    }

                    // insertar commission
                    //$total = Statistics::where([['user_id', '=', $model_id], ['range', '=', $range], ['setting_page_id', '=', 2]])->sum('value');
                    //$current_date = Carbon::now()->format('Y-m-d');

                    //$this->storeCommission($model_id, $range, $total, $current_date, $user->setting_location_id);

                    DB::commit();
                }
            }
            catch (\Exception $ex)
            {
                DB::rollBack();
            }

        }
        catch (TimeOutException $e)
        {
            $browser->quit();
            $process->stop();
        }

        return $text;
    }

    public function streamate2(){
        $pages = SettingPage::all();
        $host = 'http://localhost:9515';

        foreach($pages as $page){
            $accounts = SatelliteAccount::where('page_id', $page->id)->where('from_gb', 1)->get();
            $fromDate = Carbon::today("America/Bogota");
            $toDate = Carbon::tomorrow("Europe/London");
            $date = Carbon::today()->format("M d, Y");

            foreach($accounts as $account){
                $user = User::where('id', $account->user_id)->where('status', 1)->first();
                $value = 0;
                
                if ($user){
                    if ($page->id === 1)
                    {
                        $authorization = "Authorization: Bearer 0eab103080f4c4951bca37f66551e3f961d13dcc71258e7bbf6173e09ac3b2d7";
                        $ch = curl_init("https://partner-api.modelcenter.jasmin.com/v1/reports/performers/$account->original_nick?fromDate=$fromDate&toDate=$toDate");
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization));
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                        $result = curl_exec($ch);
                        $result1 = json_decode($result);
                        curl_close($ch);

                        if (!isset($result1->status)) {
                            $total = $result1->data->total->earnings->value;
                            $total = round($total, 2);

                            dump($account->nick, $total);
                        } else {
                            $total = 0;
                            continue;
                        }
                    }
                    elseif ($page->id === 2)
                    {
                       $url = $page->login;
                       dump($url);
                       $dashboard = "https://www.streamatemodels.com/smm/reports/earnings/EarningsReportPivot.php";
                       $username = $account->access;
                       $password = $account->password;

                        $process = (new ChromeProcess)->toProcess();
                        if ($process->isStarted()) {
                            $process->stop();
                        }
                        $process->start();

                        $options = (new ChromeOptions)->addArguments(['--disable-gpu', '--headless', '--no-sandbox', '--verbose']);
                        $capabilities = DesiredCapabilities::chrome()
                                ->setCapability(ChromeOptions::CAPABILITY, $options)->setPlatform('Linux');;
                        $driver = retry(5, function () use ($capabilities, $host) {
                            return RemoteWebDriver::create($host, $capabilities);
                        }, 1000);

                        $browser = new Browser($driver);
                        $browser->resize(1920, 1080);
                        $browser->visit($url)
                            ->type('sausr', $username)
                            ->type('sapwd', $password)
                            ->press('#login-form-submit');

                        $browser->visit($dashboard)
                            ->select('range', 'day')
                            ->value('#earnday', $date)
                            ->press('#getData');

                        try {
                            $nick = $account->nick;
                            $text = $browser->waitFor("#".$nick."-earnings > div > table > tbody > tr.odd-row.daily-summaries > td:nth-child(2)", 50)
                                ->text("#".$nick."-earnings > div > table > tbody > tr.odd-row.daily-summaries > td:nth-child(2)");

                            $text = explode('$', $text);
                            $value = $text[1];

                            dd($value);
                            
                            $this->info('**** Got =>'.$value.'****');

                            $browser->quit();
                            $process->stop();

                            try
                            {
                                DB::beginTransaction();
                                if ($value !== '0.00')
                                {
                                    $week_start = Carbon::now()->startOfWeek(Carbon::SUNDAY)->toDateString();
                                    $week_end = Carbon::now()->endOfWeek(Carbon::SATURDAY)->toDateString();
                                    $range = $week_start." / ".$week_end;
                                    $stats = Statistics::where([['user_id', '=', $user->id], ['range', '=', $range], ['setting_page_id', '=', 2]])->first();

                                    $this->info("******************inserting stats");
                                    if(!is_null($stats))
                                    {
                                        $stats->update([
                                            'value' => $value
                                        ]);
                                    }
                                    else{
                                        Statistics::create([
                                            'satellite_account_id' => $account->id,
                                            'user_id' => $user->id,
                                            'setting_page_id' => 2,
                                            'setting_location_id' => $user->setting_location_id,
                                            'value' => $value,
                                            'range' => $range,
                                            'date' => Carbon::now()->format('Y-m-d')
                                        ]);
                                    }

                                    $summary = StatisticSummary::where([['user_id', '=', $user->id], ['range', '=', $range]])->first();
                                    if (!is_null($summary))
                                    {
                                        $summary->update([
                                            'value' => $summary->value + $value
                                        ]);
                                    }
                                    else
                                    {

                                        $this->info("******************inserting summary");
                                        StatisticSummary::updateOrCreate([
                                            'user_id' => $user->id,
                                            'value' => $value,
                                            'goal' => 50.00,
                                            'record' => 150.00,
                                            'range' => $range,
                                        ]);

                                    }

                                    // insertar commission
                                    //$total = Statistics::where([['user_id', '=', $model_id], ['range', '=', $range], ['setting_page_id', '=', 2]])->sum('value');
                                    //$current_date = Carbon::now()->format('Y-m-d');

                                    //$this->storeCommission($model_id, $range, $total, $current_date, $user->setting_location_id);

                                    DB::commit();
                                }
                            }
                            catch (\Exception $ex)
                            {
                                DB::rollBack();
                            }

                        }
                        catch (TimeOutException $e)
                        {
                            $browser->quit();
                            $process->stop();
                        }
                    }
                }
            }
        }
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("inicia cron job");
        $this->streamate();
        $this->info("******************termina cron job");
    }
}
