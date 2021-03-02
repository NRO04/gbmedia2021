<?php

namespace App\Http\Controllers;

use App\Models\Attendance\Attendance;
use App\Models\Attendance\AttendanceSummary;
use App\Models\Bookings\BookingType;
use App\Models\Dashboard\Reservation;
use App\Models\News\NewsStudio;
use App\Models\Schedule\Schedule;
use App\Models\Settings\SettingLocation;
use App\Models\Settings\SettingRole;
use App\Models\Statistics\Statistics;
use App\Models\Statistics\StatisticSummary;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskComment;
use App\Models\Tasks\TaskUserFolder;
use App\Traits\TraitGlobal;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    use TraitGlobal;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user_permission = Auth()->user()->getPermissionsViaRoles()->pluck('name');

        return view('adminModules.home.dashboard')->with(compact('user_permission'));
    }

    public function getUserLastNew()
    {
        $new = [];

        $tenant_id = tenant('id');

        $last_new = NewsStudio
            ::join('news_users', 'news_users.news_id', 'news_studios.news_id')
            ->join('news', 'news.id', 'news_studios.news_id')
            ->where('news_studios.studio_id', $tenant_id)
            ->where('news_users.user_id', auth()->user()->id)
            ->where('news_users.studio_id', $tenant_id)
            ->orderBy('news_studios.id', 'DESC')->first();

        if (!is_null($last_new))
        {
            $new = [
                'id' => $last_new->id,
                'title' => $last_new->title,
                'file' => "../storage/app/public/".$last_new->file,
                'extension' => $last_new->extension,
                'body' => $this->excerpt($last_new->body, 300),
                'creation' => ucfirst(Carbon::parse($last_new->created_at)->diffForHumans()),
            ];
        }

        return response()->json($new);
    }

    public function createAgendaEvent(Request $request)
    {
        $reservation = new Reservation;
        $reservation->user_id = Auth()->user()->id;
        $reservation->title = $request->title;
        $reservation->description = $request->description;
        $time_from_explode = explode(" ", $request->time_from);
        if ($time_from_explode[1] == "pm")
        {
            $time_from = explode( ":", $time_from_explode);
            $time_from1 = $time_from[0] + 12;
            $time_from = $time_from1.":".$time_from[1];
        }
        else
        {
            $time_from = $time_from_explode[0];
        }
        $time_to_explode = explode(" ", $request->time_to);
        if ($time_to_explode[1] == "pm")
        {
            $time_to = explode( ":", $time_to_explode);
            $time_to1 = $time_to[0] + 12;
            $time_to = $time_to1.":".$time_to[1];
        }
        else
        {
            $time_to = $time_to_explode[0];
        }
        $reservation->date_from = $request->start." ".$time_from;
        $reservation->date_to = $request->end." ".$time_to;
        $reservation->time_from = $request->time_from;
        $reservation->time_to = $request->time_to;
        $reservation->color = $request->color;
        $reservation->save();

        $result['id'] = $reservation->id;
        $result['title'] = $reservation->title;
        $result['start'] = $reservation->date_from;
        $result['end'] = $reservation->date_to;
        $result['color'] = $reservation->color;
        return response()->json(['reservation' => $result]);
    }

    public function getAgendaEvents()
    {
        $reservations = Reservation::where('user_id', Auth()->user()->id)->get();
        $result = [];
        foreach ($reservations as $key => $reservation) {
            $result[$key]['id'] = $reservation->id;
            $result[$key]['title'] = $reservation->title;
            $result[$key]['start'] = $reservation->date_from;
            $result[$key]['end'] = $reservation->date_to;
            $result[$key]['color'] = $reservation->color;
        }

        return response()->json(['reservations' => $result]);
    }

    public function getUserLatestTasks()
    {
        $data = [];

        $tasks = Task::join('task_user_status', 'tasks.id', '=', 'task_user_status.task_id')
            ->select("tasks.*", 'task_user_status.status as status_user', 'task_user_status.id as task_status_id', 'task_user_status.pulsing as pulsing')
            ->where('task_user_status.user_id', '=', Auth::user()->id)
            ->where('task_user_status.status', '=', 0)
            ->where('task_user_status.folder', '=', 0)
            ->where('tasks.status', '=', 0)
            ->orderBy('pulsing', 'desc')->orderBy('task_user_status.created_at', 'desc')->limit(7)->get();

        foreach ($tasks AS $task) {
            $task_last_comment = TaskComment::where('task_id', $task->id)->orderBy('id', 'DESC')->first();

            if ($task->created_by_type == 1)
            {
                $user = User::find($task->created_by);
                $created_by = ($user->setting_role_id == 14) ? $user->nick : $user->first_name . ' ' . $user->last_name;
                $src = is_null($user->avatar) ? asset("images/svg/no-photo.svg") : global_asset("../storage/app/public/" . tenant('studio_slug') . "/avatars/" . $user->avatar);
            }
            else
            {
                $role = SettingRole::find($task->created_by);
                $created_by = $role->name;
                $src = url("assets/img/avatars/5.jpg");
            }

            $created_at = Carbon::parse($task->created_at)->format('d/M/Y h:m a');

            $data[] = [
                'id' => $task->id,
                'title' => "<b>" . $this->accents($task->title) . "</b>",
                'info' => "<span class='text-muted'>Publicado por: $created_by | $created_at</span>",
                'creator_image' => $src,
                'status_user' => $task->status_user,
                'should_finish' => $task->should_finish,
                'pulsing' => $task->pulsing,
                'created_at' => Carbon::parse($task->created_at)->toDateTimeString(),
                'last_comment_date' => Carbon::parse($task_last_comment->created_at)->diffForHumans(),
                'code' => $task->code,
                'time' => null,
                'progress_percent' => null,
                'progress_class' => null,
            ];
        }

        return response()->json($data);
    }

    public function getLocations()
    {
         $locations = SettingLocation::all();
         return response()->json($locations);
    }

    public function getAttendanceStats(Request $request)
    {
        $location = $request->get('selectedLocation');
        $today_start = $request->get('start');
        $today_end = $request->get('end');
        $at_from = Carbon::parse($today_start)->startOfWeek(Carbon::SUNDAY)->toDateString();
        $at_to = Carbon::parse($today_start)->endOfWeek(Carbon::SATURDAY)->toDateString();
        $attendance_range = $today_start." / ".$today_end;
        $best = "";
        $worst = "";
        $shifts = 6;

        if ($location === 1){
            $models = User::where('setting_role_id', 14)->where('status', 1)->get();
            $unjustified = AttendanceSummary::where('range', $attendance_range)->sum('unjustified_days');
            $studio_goal = AttendanceSummary::where('range', $attendance_range)->sum('goal');
            $studio_goal = $studio_goal * $shifts;
            
            $best_selling = StatisticSummary::where('range', $attendance_range)->max('value');
            $worst_selling = StatisticSummary::where('range', $attendance_range)->min('value');
            $reached_amount = StatisticSummary::where('range', $attendance_range)->sum('value');
            $models_earning = StatisticSummary::where('range', $attendance_range)->count();
        }
        else{
            $models = User::where('setting_role_id', 14)->where('status', 1)->where('setting_location_id', $location)->get();
            $unjustified = AttendanceSummary::where('setting_location_id', $location)->where('range', $attendance_range)->sum('unjustified_days');
            $studio_goal = AttendanceSummary::where('range', $attendance_range)->where('setting_location_id', $location)->sum('goal');
            $studio_goal = $studio_goal * $shifts;

            $best_selling = StatisticSummary::where('range', $attendance_range)->where('setting_location_id', $location)->max('value');
            $worst_selling = StatisticSummary::where('range', $attendance_range)->where('setting_location_id', $location)->min('value');
            $reached_amount = StatisticSummary::where('range', $attendance_range)->where('setting_location_id', $location)->sum('value');
            $models_earning = StatisticSummary::where('range', $attendance_range)->where('setting_location_id', $location)->count();
        }
        
        $summaries = [];
        $most = [];
        $lostArray = [];
        $earnings_per_hour = $models_earning / 3360;
        $data = [
            'unjustified' => $unjustified,
            'studio_goal' => "$".number_format($studio_goal, 2, '.', ','),
            'active_models' => count($models),
            'models_earning' => $models_earning,
            'reached_amount' => "$".number_format($reached_amount, 2, '.', ','),
            'earnings_per_hour' => "$".number_format($earnings_per_hour, 2,'.',','),
        ];

        foreach($models as $key => $model){
            $summary_model = AttendanceSummary::where('model_id', $model->id)->where('range', $attendance_range)->where('setting_location_id', $model->setting_location_id)->first();
            $unjustified_days = Attendance::where('model_id', $model->id)->where('attendance_type', 6)->where('setting_location_id', $location)->whereBetween('date', [$at_from, $at_to])->count();

            if (!is_null($summary_model)){
                $max_unjustified = $summary_model->max('unjustified_days');
                $most = AttendanceSummary::select('users.nick', 'attendance_summary.unjustified_days', 'attendance_summary.goal')
                    ->join('users', 'attendance_summary.model_id', 'users.id')
                    ->where('unjustified_days', $max_unjustified)->first();


                $best = StatisticSummary::select('statistics_summary.value', 'users.id', 'users.nick')
                    ->join('users', 'statistics_summary.user_id', 'users.id')
                    ->where('range', $attendance_range)
                    ->where('value', $best_selling)->first();

                $worst = StatisticSummary::select('statistics_summary.value', 'users.id', 'users.nick')
                    ->join('users', 'statistics_summary.user_id', 'users.id')
                    ->where('range', $attendance_range)
                    ->where('value', $worst_selling)->first();

                $lost_money  = $summary_model->goal * $summary_model->unjustified_days;
                array_push($lostArray, $lost_money);

                $summaries[] = [
                    'model_nick' => $model->nick,
                    'model_worked' => $summary_model->worked_days,
                    'model_unjustified' => $summary_model->unjustified_days,
                    'model_justified' => $summary_model->justified_days,
                    'model_period' => $summary_model->period,
                    'model_goal' => "$".number_format($summary_model->goal, 2, '.', ','),
                    'lost_money' => "$".number_format($lost_money, 2,'.',','),
                    'unjustified_days' => $unjustified_days,
                ];
            }else{
                continue;
            }
        }
        

        $sum = array_sum($lostArray);
        
        return response()->json([
            'overall' => $data,
            'summaries' => $summaries,
            'most' => $most,
            'best_selling' => $best,
            'worst_selling' => $worst,
            'lost_money' => $sum,
        ]);
    }
}
