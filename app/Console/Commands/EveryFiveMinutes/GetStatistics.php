<?php

namespace App\Console\Commands\EveryFiveMinutes;

use App\Models\Satellite\SatelliteAccount;
use App\Models\Settings\SettingPage;
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

class GetStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:statistics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command executes crawler to acquire statistics from streaming pages';

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
        $this->info("inicia cron job");
        $from = Carbon::now()->format('Y-m-d');
        $to = Carbon::tomorrow()->format('Y-m-d');
        $pages = SettingPage::select('id', 'name')->get();
        $host = 'http://localhost:9515';

        foreach ($pages as $page) {
            $this->info('*********' . $page->name . '*********');
            $accounts = SatelliteAccount::where('page_id', $page->id)->where('from_gb', 1)->get();
            $fromDate = Carbon::today("America/Bogota");
            $toDate = Carbon::tomorrow("Europe/London");
            $date = Carbon::today()->format("M d, Y");
            $stats = null;

            foreach ($accounts as $account) {
                $user = User::where('id', $account->user_id)->where('status', 1)->first();

                if (!is_null($user)) {
                    if ($page->id === 1) {
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

                            $statexist = Statistics::where('setting_page_id', $page->id)->where('user_id', $user->id)->where('date', $date)->first();
                            $satellite = SatelliteAccount::where('user_id', '=', $user->id)->first();

                            if ($total == 0) {
                                $info = $account->nick . "- " . $total;
                                $this->info($info);
                            } else {
                                if (is_null($statexist)) {
                                    $stats = Statistics::updateOrCreate([
                                        'satellite_account_id' => $satellite->id,
                                        'user_id' => $user->id,
                                        'setting_page_id' => $page->id,
                                        'setting_location_id' => $user->setting_location_id,
                                        'value' => $total,
                                        'range' => $from . " / " . $to,
                                        'date' => $date
                                    ]);
                                } else {
                                    $statexist->update([
                                        'value' => $total
                                    ]);
                                }
                                $this->info($stats);
                            }
                        } else {
                            $this->info("Account: " . $account->original_nick . " not found on Jasmin");
                            continue;
                        }
                    } elseif ($page->id === 2) {
                        $url = $page->login;
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
                            $text = $browser->waitFor("#" . $nick . "-earnings > div > table > tbody > tr.odd-row.daily-summaries > td:nth-child(2)", 50)
                                ->text("#" . $nick . "-earnings > div > table > tbody > tr.odd-row.daily-summaries > td:nth-child(2)");

                            $text = explode('$', $text);
                            $value = $text[1];

                            $this->info('**** Got =>' . $value . '****');

                            $browser->quit();
                            $process->stop();

                            try {
                                DB::beginTransaction();
                                if ($value !== '0.00' || $value !== 0) {
                                    $week_start = Carbon::now()->startOfWeek(Carbon::SUNDAY)->toDateString();
                                    $week_end = Carbon::now()->endOfWeek(Carbon::SATURDAY)->toDateString();
                                    $range = $week_start . " / " . $week_end;
                                    $stats = Statistics::where([['user_id', '=', $user->id], ['range', '=', $range], ['date', '=', $date], ['setting_page_id', '=', 2]])->first();

                                    $this->info("******************inserting stats");
                                    if (!is_null($stats)) {
                                        $stats->update([
                                            'value' => $value
                                        ]);
                                    } else {
                                        Statistics::updateOrCreate([
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
                                    if (!is_null($summary)) {
                                        $summary->update([
                                            'value' => $summary->value + $value
                                        ]);
                                    } else {

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
                            } catch (\Exception $ex) {
                                DB::rollBack();
                            }
                        } catch (TimeOutException $e) {
                            $browser->quit();
                            $process->stop();
                        }
                    }
                } else {
                    $this->info("Account: " . $account->original_nick . " not found on GB Platform");
                }
            }
        }

        $this->info("******************termina cron job");
    }
}
