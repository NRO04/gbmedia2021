<?php

namespace App\Http\Controllers\Statistics;

use App\Http\Controllers\Controller;
use App\Models\Attendance\Attendance;
use App\Models\Attendance\AttendanceSummary;
use App\Models\monitoring\Monitoring;
use App\Models\Satellite\SatelliteAccount;
use App\Models\Settings\SettingPage;
use App\Models\Statistics\Statistics;
use App\Models\Statistics\StatisticSummary;
use Carbon\Carbon;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Exception\TimeOutException;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Hamcrest\Core\Set;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Laravel\Dusk\Chrome\ChromeProcess;
use App\User;

//java -jar selenium-server-standalone-3.141.59.jar
class StatisticsController extends Controller
{

    public function getStatistics(Request $request)
    {
        $start = $request->get('start');
        $end = $request->get('end');
        $location_id = $request->get('selectedLocation');
        $from = Carbon::parse($start);
        $to = Carbon::parse($end);
        $data = [];
        $dates = [];
        $range = $start." / ".$end;;
        
        $data['columns'][] = ['key' => 'nick', 'label' => 'Modelo', 'sortable' => true, 'sortDirection' => 'asc'];
        for($d = $from; $d->lte($to); $d->addDay()) {
            $dates[] = $d->format('Y-m-d');
            if (strtolower($d->format('l')) === 'sunday') {
                $label = 'D ('.$d->format('Y-m-d').')';
            }
            elseif (strtolower($d->format('l')) === 'monday') {
                $label = 'L ('.$d->format('Y-m-d').')';
            }
            elseif (strtolower($d->format('l')) === 'tuesday') {
                $label = 'M ('.$d->format('Y-m-d').')';
            }
            elseif (strtolower($d->format('l')) === 'wednesday') {
                $label = 'X ('.$d->format('Y-m-d').')';
            }
            elseif (strtolower($d->format('l')) === 'thursday') {
                $label = 'J ('.$d->format('Y-m-d').')';
            }
            elseif (strtolower($d->format('l')) === 'friday') {
                $label = 'V ('.$d->format('Y-m-d').')';
            }
            elseif (strtolower($d->format('l')) === 'saturday') {
                $label = 'S ('.$d->format('Y-m-d').')';
            }
            $data['columns'][] = ['key' => strtolower($d->format('l')), 'label' => $label];
        }

        $statistics = Statistics::select('user_id')->where([
            ['setting_location_id', '=', $location_id],
            ['range', '=', $range]
        ])->groupBy('user_id')->get();

        foreach ($statistics as $count =>  $statistic)
        {
            $count_goal_1 = 0;
            $count_goal_2  = 0;
            $count_goal_3  = 0;

            $model = User::where('id', $statistic->user_id)->first();
            $total = Statistics::where([['user_id', '=', $statistic->user_id], ['range', '=', $range]])->sum('value');
            $day_goal = Statistics::where([['user_id', '=', $statistic->user_id], ['date', '=', Carbon::now()->format('Y-m-d')]])->sum('value');
            $day_value =  !is_null($day_goal) ? $day_goal : 0;
            $model_goal = AttendanceSummary::where('model_id', $model->id)->where('range', $range)->first();
            if (!is_null($model_goal)){
                $goal_one_value = $model_goal->goal;
            }else{
                $goal_one_value = 50.00;
            }

            $goal_two_value = round($goal_one_value + 50.00, 2);
            $goal_three_value = round($goal_one_value + 100.00,2);
            
            foreach ($dates as $dayCount => $date)
            {
                $s = Statistics::select(DB::raw("SUM(value) as total_amount"))
                    ->where([
                        ['user_id', '=', $model->id],
                        ['date', '=', $date]
                    ])->first();

                if($s->total_amount >= $goal_one_value) {
                    $count_goal_1++;
                }

                if($s->total_amount >= $goal_two_value) {
                    $count_goal_2++;
                }

                if($s->total_amount >= $goal_three_value) {
                    $count_goal_3++;
                }

                $data['statistics'][$count]['nick'] = $model->nick;
                $data['statistics'][$count]['model_id'] = $model->id;

                $data['statistics'][$count]['daily_goal_one'] = number_format($goal_one_value, 2, ',', '.'). " / ( ".$count_goal_1. " )";
                $data['statistics'][$count]['daily_goal_two'] =  number_format($goal_two_value, 2, ',', '.'). " / ( ".$count_goal_2. " )";
                $data['statistics'][$count]['daily_goal_three'] = number_format($goal_three_value, 2, ',', '.'). " / ( ".$count_goal_3." )";

                $data['columns'][] = ['key'=>'daily_goal_one', 'label' => '1ra', 'class' => 'text-center'];
                $data['columns'][] = ['key'=>'daily_goal_two', 'label' => '2da', 'class' => 'text-center'];
                $data['columns'][] = ['key'=>'daily_goal_three', 'label' => '3ra', 'class' => 'text-center'];


                if (!is_null($s->total_amount)){
                    $percentage = ($s->total_amount * 100) / $goal_one_value;
                    if ($percentage < 50){
                        $variant = "outline-danger";
                    }
                    elseif ($percentage > 50 && $percentage <= 90){
                        $variant = "outline-warning";
                    }else{
                        $variant = "outline-success";
                    }
                }else{
                    $variant = "outline-secondary";
                }

                $data['statistics'][$count][strtolower(Carbon::parse($dates[$dayCount])->format('l'))] = [
                    'value' => is_null($s->total_amount) ? "$0.00" : "$". round($s->total_amount, 2),
                    'variant' => $variant,
                    'date' => $date
                ];
            }

            $data['columns'][] = ['key' => 'weekly_performance', 'label' => '% Semana', 'class' => 'text-center'];
            $data['statistics'][$count]['weekly_performance'] =  "$".$total." (".round(($total * 100) / ($goal_one_value * 7), 2) ."%".")";
        }
        return response()->json($data);
    }

    public function getPages(Request $request)
    {

        $id = $request->get('model_id');
        $date = $request->get('date');

        $data = [];
        $pages = SatelliteAccount::select('setting_pages.name', 'setting_pages.id')
             ->join('setting_pages', 'setting_pages.id', '=', 'satellite_accounts.page_id')
             ->where('satellite_accounts.user_id', '=', $id)->where('satellite_accounts.from_gb', '=', 1)->get();

        $edit_stats = false;
        if (auth()->user()->can('statistics-edit')){
            $edit_stats = true;
        }

        foreach ($pages as $key => $page){
             $page_stats = Statistics::where([['setting_page_id', '=', $page->id], ['date', '=', $date], ['user_id', '=', $id]])->first();
             $data[$key] = [
               'id' => $page->id,
               'name' => $page->name,
               'enabled' => $edit_stats,
               'value' => is_null($page_stats) ? "" : $page_stats->value
             ];
        }
        return response()->json($data);
    }

    public function saveStatistics(Request $request)
    {
        $pages = $request->input('pages');
        $date = $request->input('date');
        $model_id = $request->input('model_id');
        $start = $request->get('start');
        $end = $request->get('end');
        $range = $start." / ".$end;
        $stats = "";
        
        try
        {
            DB::beginTransaction();

            $satellite = SatelliteAccount::where('user_id', '=', $model_id)->first();
            $user = User::where('id', '=', $model_id)->first();

            foreach($pages as $page)
            {
                Statistics::where('setting_page_id', $page['id'])->where('user_id', $model_id)->where('date', $date)->delete();

                if (!empty($page['value']))
                {
                    $stats = Statistics::updateOrCreate([
                        'satellite_account_id' => $satellite->id,
                        'user_id' => $model_id,
                        'setting_page_id' => $page['id'],
                        'setting_location_id' => $user->setting_location_id,
                        'value' => $page['value'],
                        'range' => $start." / ".$end,
                        'date' => $date
                    ]);
                }else{
                    $msg = "Ha ocurrido un error, por favor, comuniquese con el admin";
                    $icon = "error";
                    $code = 500;
                }
            }

            $attendance_exists = Attendance::where('model_id', $model_id)->where('date', Carbon::now()->format('Y-m-d'))->first();
            $monitoring_exists = Monitoring::where('model_id', $model_id)->where('date', Carbon::now()->format('Y-m-d'))->exists();
            if(!is_null($attendance_exists)){
                if ($monitoring_exists === false){
                    Monitoring::updateOrCreate([
                        'model_id' => $model_id,
                        'range' => $range,
                        'date' => Carbon::now()->format('Y-m-d'),
                        'status' => 0,
                        'setting_location_id' => $user->setting_location_id
                    ]);
                }
            }

            $total = Statistics::where([['user_id', '=', $model_id], ['range', '=', $range]])->sum('value');
            $goal = AttendanceSummary::where('model_id', $model_id)->where('range', $range)->first();

            $statsSummary = StatisticSummary::where('user_id', $model_id)->where('range', $range)->where('setting_location_id', $user->setting_location_id)->first();
            if (!is_null($statsSummary)){
                $statsSummary->update([
                    'value' => $total,
                ]);
            }else{
                StatisticSummary::updateOrCreate([
                    'user_id' => $model_id,
                    'value' => $total,
                    'setting_location_id' => $user->setting_location_id,
                    'goal' => $goal->goal,
                    'record' => 150.00,
                    'range' => $range,
                ]);
            }

            $msg = "Estadistica actualizada";
            $icon = "success";
            $code = 200;
            DB::commit();
        }
        catch (\Exception $ex)
        {
            DB::rollBack();

            $msg = "Ha ocurrido un error, por favor, comuniquese con el admin".$ex->getMessage();
            $icon = "error";
            $code = 500;
        }

        return response()->json([
            'msg' => $msg,
            'icon' => $icon,
            'code' => $code,
        ]);
    }

    public function getModels($id)
    {
        $models = User::where([['setting_role_id', 14], ['setting_location_id', $id],['status', 1]])->get();
        return response()->json($models);
    }

    public function AllPages($id)
    {
        $pages = SatelliteAccount::select('setting_pages.name', 'setting_pages.id')
            ->join('setting_pages', 'setting_pages.id', '=', 'satellite_accounts.page_id')
            ->where('satellite_accounts.user_id', '=', $id)->where('satellite_accounts.from_gb', '=', 1)->get();

        $edit_stats = false;
        if (auth()->user()->can('statistics-edit')){
            $edit_stats = true;
        }

        $dates = [];
        $week_start = Carbon::now()->startOfWeek(Carbon::SUNDAY)->toDateString();
        $week_end = Carbon::now()->endOfWeek(Carbon::SATURDAY)->toDateString();
        $from = Carbon::parse($week_start);
        $to = Carbon::parse($week_end);
        for($d = $from; $d->lte($to); $d->addDay()) {
            $dates[] = $d->format('Y-m-d');
        }
        
        $model = User::where('id', $id)->first();
        return response()->json([
            'pages' => $pages,
            'dates' => $dates,
            'nick' => $model->nick,
            'enabled' => $edit_stats,
        ]);
    }


    // crawler
    protected function streamate()
    {
        $url = "https://www.streamatemodels.com/smm/login.php";
        $dashboard = "https://www.streamatemodels.com/smm/reports/earnings/EarningsReportPivot.php";
        $username = "LFMM1@YOPMAIL.COM";
        $password = "Molina89";
        $text = "";
        $host = 'http://localhost:9515';
//        $host = 'http://localhost:4444/wd/hub';

        $process = (new ChromeProcess)->toProcess();
        if ($process->isStarted()) {
            $process->stop();
        }
        $process->start();

        $options = (new ChromeOptions)->addArguments(['--disable-gpu', '--headless', '--no-sandbox']);
        $capabilities = DesiredCapabilities::chrome()
            ->setCapability(ChromeOptions::CAPABILITY, $options);
//            ->setPlatform('Linux');
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
            ->value('#earnday', 'Dec 15, 2020')
            ->press('#getData');

        try {
            $nick = "AlyssonCarter";
            $text = $browser->waitFor("#".$nick."-earnings > div > table > tbody > tr.odd-row.daily-summaries > td:nth-child(2)", 50)
                ->text("#".$nick."-earnings > div > table > tbody > tr.odd-row.daily-summaries > td:nth-child(2)");

            $browser->quit();
            $process->stop();
        } catch (TimeOutException $e) {
            $browser->quit();
            $process->stop();
        }

        return response()->json($text);
    }

    protected function imlive()
    {
        $url = "https://host.imlive.com/login.asp";
        $dashboard = "https://host.imlive.com/account.asp";
        $username = "gbmedia480";
        $password = "Molina89";
        $text = "";
        $host = 'http://localhost:9515';

        $process = (new ChromeProcess)->toProcess();
        if ($process->isStarted()) {
            $process->stop();
        }
        $process->start();

        $options = (new ChromeOptions)->addArguments(['--disable-gpu', '--headless', '--no-sandbox']);
        $capabilities = DesiredCapabilities::chrome()
            ->setCapability(ChromeOptions::CAPABILITY, $options);
//            ->setPlatform('Linux');
        $driver = retry(5, function () use ($capabilities, $host) {
            return RemoteWebDriver::create($host, $capabilities);
        }, 50);

        $browser = new Browser($driver);
        $browser->resize(1920, 1080);
        $browser->visit($url)
            ->type('login', $username)
            ->type('password', $password)
            ->press('btnSubmit');

        $browser->visit($dashboard);

        try {
            $text = $browser->waitFor("tr.total > td:nth-child(2)")
                ->text("tr.total > td:nth-child(2)");
            $browser->quit();
            $process->stop();
        } catch (TimeOutException $e) {
            $browser->quit();
            $process->stop();
        }

        return response()->json($text);
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
            ->setCapability(ChromeOptions::CAPABILITY, $options);
//            ->setPlatform('Linux');
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

            $browser->quit();
            $process->stop();
        } catch (TimeOutException $e) {
            $browser->quit();
            $process->stop();
        }

        return response()->json($text);
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
        $capabilities = DesiredCapabilities::chrome();
//            ->setCapability(ChromeOptions::CAPABILITY, $options);
//            ->setPlatform('Linux');
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

    
    //scripts
    protected function execute2()
    {
        $min_id = 140001;
        $max_id = 150000;
       /* $min_id = 137000;
        $max_id = 137344;*/
        $stats = DB::connection('gbmedia')->table('estadistica_1')
            ->whereBetween('id', [$min_id, $max_id])->get();
        $msg = "nothing bitch";
//        dd($stats);

        foreach ($stats as $stat)
        {
            $from = Carbon::parse($stat->fecha)->startOfWeek(Carbon::SUNDAY)->toDateString();
            $to = Carbon::parse($stat->fecha)->endOfWeek(Carbon::SATURDAY)->toDateString();

//            $model = User::where('old_user_id', $stat->usuario_id)->first();
            $model = User::where('old_user_id', $stat->usuario_id)->first();
            $pagina = DB::connection('gbmedia')->table('paginas')->where('id', $stat->pagina_id)->first();
            if (is_null($pagina)){
                continue;
            }
            $page = SettingPage::where('name', $pagina->nombre)->first();

            $saccount = SatelliteAccount::where('user_id', $model->id)->first();
            /*dump($pagina->nombre);
            continue;*/

            if (is_null($saccount)){
                continue;
            }

            $first_stats = Statistics::firstOrCreate(
                [
                    'user_id' => $model->id,
                    'setting_page_id' => $page->id,
                    'range' => $from." / ".$to,
                    'date' => $stat->fecha,
                ],
                [
                    'satellite_account_id' => $saccount->id,
                    'user_id' => $model->id,
                    'setting_page_id' => $page->id,
                    'setting_location_id' => $model->setting_location_id,
                    'value' => $stat->resta,
                    'range' => $from." / ".$to,
                    'date' => $stat->fecha,
                ]
            );

            $first_stats->satellite_account_id = $saccount->id;
            $first_stats->user_id = $model->id;
            $first_stats->setting_page_id = $page->id;
            $first_stats->setting_location_id = $model->setting_location_id;
            $first_stats->value = $stat->resta;
            $first_stats->range = $from." / ".$to;
            $first_stats->date = $stat->fecha;
            $ok_first_stats = $first_stats->save();


            $total = Statistics::where([['user_id', '=', $model->id], ['range', '=', $from." / ".$to]])->sum('value');

            $first_summary = StatisticSummary::firstOrCreate(
                [
                    'user_id' => $model->id,
                    'range' => $from." / ".$to,
                ],
                [
                    'user_id' => $model->id,
                    'value' => $total,
                    'goal' => 50.00,
                    'record' => 150.00,
                    'range' => $from." / ".$to,
                ]
            );

            $first_summary->user_id = $model->id;
            $first_summary->value = $total;
            $first_summary->goal = 50.00;
            $first_summary->record = 150.00;
            $first_summary->range = $from." / ".$to;
            $ok_first_comment = $first_summary->save();

            $msg = "done bitch";
        }

        return response()->json($msg);
    }

    protected function pageurl()
    {
        $min_id = 1;
        $max_id = 40;
        $pages = DB::connection('gbmedia')->table('paginas')
            ->whereBetween('id', [$min_id, $max_id])->get();
        $msg = "Nothing bitch";

        foreach($pages as $page)
        {
            $tipo = 0;
            $settingpage = SettingPage::where('name', $page->nombre)->first();
           if ($page->tipo === "diario"){
                $tipo = 1;
           }elseif ($page->tipo === "semanal"){
                $tipo = 2;
           }else{
               $tipo = 3;
           }

           if ($page->comienza_semana === "Domingo"){
                 $starts = 0;
           }elseif($page->comienza_semana === "Lunes"){
               $starts = 1;
           }elseif($page->comienza_semana === "Sabado"){
               $starts = 6;
           }else{
               $starts = NULL;
           }

            if ($page->termina_semana === "Domingo"){
                $ends = 0;
            }elseif($page->termina_semana === "Sabado"){
                $ends = 6;
            }else{
                $ends = NULL;
            }

           $settingpage->update([
              'type' => $tipo,
              'position' => $page->posicion,
              'admin_status' => $page->admin,
              'login' => $page->url_login,
              'start_week' => $starts,
              'finish_week' => $ends,
              'start_num1' => ($page->comienza_numero1 === "NULL") ? NULL : $page->comienza_numero1,
              'finish_num1' => ($page->termina_numero1 === "NULL") ? NULL : $page->termina_numero1,
              'start_num2' => ($page->comienza_numero2  === "NULL") ? NULL : $page->comienza_numero2,
              'finish_num2' => ($page->termina_numero2 === 'NULL') ? NULL : $page->termina_numero2,
           ]);

            $msg = "Nothing bitch";
        }

        return response()->json($msg);
    }

    protected function goals()
    {
        $min_id = 1;
        $max_id = 564;
        $goals = DB::connection('gbmedia')
            ->table('metas')
            ->whereBetween('id', [$min_id, $max_id])
            ->get();
        $msg = "Nothing bitch";

        $from = Carbon::now()->startOfWeek(Carbon::SUNDAY)->toDateString();
        $to = Carbon::now()->endOfWeek(Carbon::SATURDAY)->toDateString();

        foreach ($goals as $goal){
            $model = User::where('old_user_id', $goal->me_fk_u_id)->where('status', 1)->first();
//            $model = User::where('old_user_id', 440)->where('status', 1)->first();
            if (!is_null($model)){

                $summary = AttendanceSummary::where('model_id', $model->id)->where('range', '=', $from." / ".$to)->first();
                if (!is_null($summary)){
                    $summary->update([
                        'goal' => $goal->meta1
                    ]);
                }
                
                $msg = "Done bitch";
            }
        }

        return response()->json($msg);
    }

    public function execute3()
    {
        $min_id = 1;
        $max_id = 30000;
        $date = "2018-12-30";
        $up = "";
        $goal = 50.00;
        $from = Carbon::parse($date)->startOfWeek(Carbon::SUNDAY)->toDateString();
        $to = Carbon::parse($date)->endOfWeek(Carbon::SATURDAY)->toDateString();
        $range = $from." / ".$to;
        $models = User::where('setting_role_id', 14)->whereBetween('id', [$min_id, $max_id])->get();
        foreach ($models as $model){
            $summary = AttendanceSummary::where('model_id', $model->id)->where('range', $range)->first();
            if (!is_null($summary)){
                $goal = $summary->goal;
            }

            $total = Statistics::where([['user_id', '=', $model->id], ['range', '=', $range]])->sum('value');
            if (is_null($total) || $total <= 0){
                continue;
            }
            echo "<br>".$total."->".$model->id;
            $up = StatisticSummary::updateOrCreate([
                'user_id' => $model->id,
                'setting_location_id' => $model->setting_location_id,
                'value' => $total,
                'goal' => $goal,
                'record' => 150.00,
                'range' => $range,
                'created_at' => '2021-01-13 10:18:57',
                'updated_at' => '2021-01-13 10:18:57',
            ]);
        }

        return response()->json($up);
    }

    public function execute()
    {
        $date = "2019-12-28";
        $from = Carbon::parse($date)->startOfWeek(Carbon::SUNDAY)->toDateString();
        $to = Carbon::parse($date)->endOfWeek(Carbon::SATURDAY)->toDateString();
        $range = $from." / ".$to;
        $attendance = Attendance::whereNull('setting_location_id')->where('range', $range)->get();
        $msg = "nothing";

        foreach ($attendance as $at){
            $msg = $at;
            $user = User::where('id', $at->model_id)->first();
            $at->update([
                'setting_location_id' => $user->setting_location_id
            ]);
            $msg = $at;
        }

        return response()->json($msg);
    }

}
