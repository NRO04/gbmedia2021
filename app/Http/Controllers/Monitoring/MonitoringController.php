<?php

namespace App\Http\Controllers\Monitoring;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tasks\TaskController;
use App\Models\Attendance\Attendance;
use App\Models\Attendance\AttendanceComment;
use App\Models\Attendance\AttendanceSummary;
use App\Models\monitoring\Monitoring;
use App\Models\monitoring\MonitoringArchives;
use App\Models\monitoring\MonitoringImages;
use App\Models\monitoring\MonitoringQualification;
use App\Models\Satellite\SatelliteAccount;
use App\Models\Satellite\SatelliteOwner;
use App\Models\Schedule\Schedule;
use App\Models\Schedule\ScheduleSessions;
use App\Models\Schedule\ScheduleSessionTypes;
use App\Models\Settings\SettingLocation;
use App\Models\Statistics\Statistics;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskComment;
use App\Traits\TraitGlobal;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class MonitoringController extends Controller
{
    use TraitGlobal;

    function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user_permission = Auth()->user()->getPermissionsViaRoles()->pluck('name');

        return view('adminModules.monitoring.index', compact('user_permission'));
    }

    public function locations()
    {
        $locations = $this->userLocationAccess();

//        $locations = SettingLocation::all();
        return response()->json($locations);
    }

    public function rooms($id)
    {
        $rooms = SettingLocation::select('rooms')->where('id', '=', $id)->first();
        return response()->json($rooms->rooms);
    }

    public function sessions()
    {
        $sessions = ScheduleSessionTypes::all();
        return response()->json($sessions);
    }

    /*
       Attendance
   */
    public function allAttendances(Request $request)
    {
        $data = [];
        $dates = [];
        $days = [];
        $label = "";

        $start_date = $request->get('start');
        $end_date = $request->get('end');

        if (auth()->user()->hasPermissionTo('monitoring-view')){
            $data['columns'][] = ['key' => 'nick', 'label' => 'Modelo', 'sortable' => true, 'sortDirection' => 'asc'];
            if ($start_date == Carbon::now()->format('Y-m-d')){
                $week_start = Carbon::now()->startOfWeek(Carbon::SUNDAY)->toDateString();
                $week_end = Carbon::now()->endOfWeek(Carbon::SATURDAY)->toDateString();
            }else{
                $week_start = $start_date;
                $week_end = $end_date;
            }

            $from = Carbon::parse($week_start);
            $to = Carbon::parse($week_end);

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

            $data['columns'][] = ['key' => 'time', 'label' => 'Debido'];
            $data['columns'][] = ['key' => 'total_recovery_minutes', 'label' => 'Recuperado'];
            $data['columns'][] = ['key' => 'action', 'label' => 'Resumen'];
            $data['weeks'] = $dates;

            $range = $request->get('start'). " / " .$request->get('end');
            $location = $request->get('selectedLocation');
            $session = $request->get('selectedSession');

            if ($session == 0) {
                $attendances = User::join('attendance_summary', 'attendance_summary.model_id', '=', 'users.id')
                    ->select(
                        'users.id',
                        'users.nick',
                        'attendance_summary.id as summary_id',
                        'attendance_summary.model_id',
                        'attendance_summary.range',
                        'attendance_summary.date',
                        'attendance_summary.worked_days',
                        'attendance_summary.unjustified_days',
                        'attendance_summary.justified_days',
                        'attendance_summary.period',
                        'attendance_summary.total_minutes',
                        'attendance_summary.total_recovery_minutes',
                        'attendance_summary.goal',
                        'attendance_summary.created_by'
                    )
                    ->where([
                        ['attendance_summary.range', '=', $range],
                        ['users.setting_location_id', '=', $location]
                    ])->get();
            }
            else {
                $attendances = Schedule::join('attendance_summary', 'attendance_summary.model_id', '=', 'schedules.user_id')
                    ->join('users', 'schedules.user_id', '=', 'users.id')
                    ->select(
                        'users.nick',
                        'schedules.id as schedule_id',
                        'schedules.session',
                        'schedules.user_id',
                        'schedules.mon',
                        'schedules.tue',
                        'schedules.wed',
                        'schedules.thu',
                        'schedules.fri',
                        'schedules.sat',
                        'schedules.sun',
                        'attendance_summary.id as summary_id',
                        'attendance_summary.model_id',
                        'attendance_summary.range',
                        'attendance_summary.date',
                        'attendance_summary.worked_days',
                        'attendance_summary.unjustified_days',
                        'attendance_summary.justified_days',
                        'attendance_summary.period',
                        'attendance_summary.total_minutes',
                        'attendance_summary.total_recovery_minutes',
                        'attendance_summary.goal',
                        'attendance_summary.created_by'
                    )
                    ->where([
                        ['attendance_summary.range', '=', $range],
                        ['users.setting_location_id', '=', $location],
                        ['schedules.session', '=', $session]
                    ])->get();
            }

            foreach ($attendances as $attendance){
                $comment = "";
                $day_id =  0;
                $attendance_id = 0;
                $icon = "";
                $variant = "";
                $attendance_type = "";
                $comment_type = "";
                $tip = "";
                $created_at = "";
                $updated_at = "";
                $has_unjustified_attendance = false;
                $today = Carbon::today()->format('Y-m-d');
                $yesterday = Carbon::yesterday()->format('Y-m-d');

                foreach ($dates as $date){
                    $due = 0;
                    $recover = 0;
                    $isDisabled = false;
                    $has_attendance = Attendance::with('comments')
                        ->where('model_id', '=', $attendance->model_id)
                        ->where('date', '=', $date)
                        ->first();

                    if (!is_null($has_attendance)){
                        $type = AttendanceComment::where('attendance_id', $has_attendance->id)
                            ->where('attendance_status_id', '=', 2)->latest()->first();

                        if ($has_attendance->attendance_type == 1) {
                            $icon = "<i class='fas fa-calendar-check'></i>";
                            $variant = "outline-success";
                            $due = $has_attendance->attendance_minutes;
                            $recover = $has_attendance->recovery_minutes;
                            $attendance_type = $has_attendance->attendance_type;
                            $attendance_id = $has_attendance->id;
                            $comment = $has_attendance->comments;
                            $comment_type = is_null($type) ? 0 : $type;
                            $tip = "Llegada";
                        }
                        elseif ($has_attendance->attendance_type == 2) {
                            $icon = "<i class='fas fa-calendar-check'></i>";
                            $variant = "outline-success";
                            $due = $has_attendance->attendance_minutes;
                            $recover = $has_attendance->recovery_minutes;
                            $attendance_type = $has_attendance->attendance_type;
                            $attendance_id = $has_attendance->id;
                            $comment = $has_attendance->comments;
                            $comment_type = is_null($type) ? 0 : $type;
                            $tip = "Conexion";
                        }
                        elseif ($has_attendance->attendance_type == 3) {
                            $icon = "<i class='fas fa-tint'></i>";
                            $variant = "outline-danger";
                            $due = $has_attendance->attendance_minutes;
                            $recover = $has_attendance->recovery_minutes;
                            $attendance_type = $has_attendance->attendance_type;
                            $attendance_id = $has_attendance->id;
                            $comment = $has_attendance->comments;
                            $comment_type = is_null($type) ? 0 : $type;
                            $tip = "Periodo";
                        }
                        elseif ($has_attendance->attendance_type == 4) {
                            $icon = "<i class='fas fa-first-aid'></i>";
                            $variant = "outline-warning";
                            $due = $has_attendance->attendance_minutes;
                            $recover = $has_attendance->recovery_minutes;
                            $attendance_type = $has_attendance->attendance_type;
                            $attendance_id = $has_attendance->id;
                            $comment = $has_attendance->comments;
                            $comment_type = is_null($type) ? 0 : $type;
                            $tip = "Enferma";
                        }
                        elseif ($has_attendance->attendance_type == 5) {
                            $icon = "<i class='fas fa-times'></i>";
                            $variant = "outline-info";
                            $due = $has_attendance->attendance_minutes;
                            $recover = $has_attendance->recovery_minutes;
                            $attendance_type = $has_attendance->attendance_type;
                            $attendance_id = $has_attendance->id;
                            $comment = $has_attendance->comments;
                            $comment_type = is_null($type) ? 0 : $type;
                            $tip = "Falta justificada";
                        }
                        elseif ($has_attendance->attendance_type == 6) {
                            $icon = "<i class='fas fa-calendar-times'></i>";
                            $variant = "outline-danger";
                            $due = $has_attendance->attendance_minutes;
                            $recover = $has_attendance->recovery_minutes;
                            $attendance_type = $has_attendance->attendance_type;
                            $attendance_id = $has_attendance->id;
                            $comment = $has_attendance->comments;
                            $comment_type = is_null($type) ? 0 : $type;
                            $tip = "Falta injustificada";
                            $has_unjustified_attendance = true;
                        }
                        elseif ($has_attendance->attendance_type == 7) {
                            $icon = "<i class='fas fa-stopwatch'></i>";
                            $variant = "outline-danger";
                            $due = $has_attendance->attendance_minutes;
                            $recover = $has_attendance->recovery_minutes;
                            $attendance_type = $has_attendance->attendance_type;
                            $attendance_id = $has_attendance->id;
                            $comment = $has_attendance->comments;
                            $comment_type = is_null($type) ? 0 : $type;
                            $tip = "Desconexion";
                        }
                        elseif ($has_attendance->attendance_type == 8) {
                            $icon = "<i class='fas fa-bed'></i>";
                            $variant = "outline-info";
                            $due = $has_attendance->attendance_minutes;
                            $recover = $has_attendance->recovery_minutes;
                            $attendance_type = $has_attendance->attendance_type;
                            $attendance_id = $has_attendance->id;
                            $comment = $has_attendance->comments;
                            $comment_type = is_null($type) ? 0 : $type;
                            $tip = "Descanso";
                        }
                        elseif ($has_attendance->attendance_type == 9) {
                            $icon = "<i class='fas fa-plane'></i>";
                            $variant = "outline-success";
                            $due = $has_attendance->attendance_minutes;
                            $recover = $has_attendance->recovery_minutes;
                            $attendance_type = $has_attendance->attendance_type;
                            $attendance_id = $has_attendance->id;
                            $comment = $has_attendance->comments;
                            $comment_type = is_null($type) ? 0 : $type;
                            $tip = "Vacaciones";
                            $isDisabled = true;
                        }
                        elseif ($has_attendance->attendance_type == 11) {
                            $recover = $has_attendance->recovery_minutes;
                            $tip = "Trabaja dia libre";
                            $icon = "<i class='fas fa-stopwatch'></i>";
                            $variant = "outline-secondary";
                        }
                        elseif ($has_attendance->attendance_type == 12) {
                            $recover = $has_attendance->recovery_minutes;
                            $comment = $has_attendance->comments;
                            $due = $has_attendance->attendance_minutes;
                            $comment_type = is_null($type) ? 0 : $type;
                            $tip = "Tiempo extra";
                            $variant = "outline-secondary";
                            $icon = "<i class='fas fa-stopwatch'></i>";
                        }
                        elseif ($has_attendance->attendance_type == 13) {
                            $recover = $has_attendance->recovery_minutes;
                            $comment = $has_attendance->comments;
                            $due = $has_attendance->attendance_minutes;
                            $comment_type = is_null($type) ? 0 : $type;
                            $tip = "Doble turno";
                            $variant = "outline-secondary";
                            $icon = "<i class='fas fa-stopwatch'></i>";
                        }
                        elseif ($has_attendance->attendance_type == 14) {
                            $recover = $has_attendance->recovery_minutes;
                            $comment = $has_attendance->comments;
                            $comment_type = is_null($type) ? 0 : $type;
                            $tip = "Error cometido";
                            $variant = "outline-secondary";
                            $icon = "<i class='fas fa-stopwatch'></i>";
                        }
                    }
                    else{
                        $icon = "<i class='fas fa-stopwatch'></i>";
                        $variant = "outline-secondary";
                        $tip = "";
                        $attendance_id = 0;
                        $comment = "Sin asistencia registrada";
                        $attendance_type = 0;
                        $is_attendance = $has_attendance === null ? false : true;

                        if (($date === $today || $date === $yesterday) || $is_attendance){
                            $isDisabled = false;
                        }
                        else{
                            $isDisabled = true;
                        }
                    }

                    $days[strtolower(Carbon::parse($dates[$day_id])->format('l'))] = [
                        'icon' => $icon,
                        'date' => $date,
                        'variant' => $variant,
                        'recover' => $recover,
                        'due' => $due,
                        'has_attendance' => $has_attendance === null ? false : true,
                        'attendance_type' => $attendance_type,
                        'attendance_id' => $attendance_id,
                        'tip' => $tip,
                        'day' => strtolower(Carbon::parse($date)->format('l')),
                        'created_at' => $created_at,
                        'updated_at' => $updated_at,
                        'comment' => $comment,
                        'comment_type' => $comment_type,
                        'isDisabled' => $isDisabled
                    ];

                    $day_id++;
                }

                $data['attendances'][] = [
                    'schedule_id' => $attendance->schedule_id,
                    'summary_id' => $attendance->summary_id,
                    'model_id' => $attendance->model_id,
                    'session' => $attendance->session,
                    'nick' => $attendance->nick,
                    'time' => '<span class="text-danger font-weight-bold">'. $attendance->total_minutes .'</span>',
                    'total_recovery_minutes' => '<span class="text-success font-weight-bold">'. $attendance->total_recovery_minutes .'</span>',
                    'isUnjustified' => $has_unjustified_attendance,
                    'days' => $days,
                    'range' => $range
                ];
            }

        }
        else{
            $data['attendances'][] = ['text' => 'Usted no tiene permiso para ver este area'];
        }

        return response()->json($data);
    }

    public function storeAttendance(Request $request)
    {
        $schedule = Schedule::where('user_id', $request->get('model_id'))->first();
        $model_id = $request->get('model_id');
        $type = $request->get('type');
        $subType = $request->get('subtype');
        $summary_id = $request->get('summary_id');
        $range = $request->get('range');
        $minuteTypes = $request->get('minutes_type');

        if($request->has('text')){
            $text = $request->get('text');
        }
        else{
            $text = NULL;
        }

        if($request->has('minutes')){
            $minutes = $request->get('minutes');
        }
        else{
            $minutes = 0;
        }

        if($request->has('from') && $request->has('to')){
            $from = $request->get("from");
            $to = $request->get('to');
        }
        else{
            $from = NULL;
            $to = NULL;
        }

        if(!is_null($request->get('date'))){
            $date = $request->get('date');
        }
        else{
            $date = Carbon::now()->format('Y-m-d');
        }

        if ($request->input('attendanceOptions') == 0){

            if ($request->get('type') === 3){
                $result = $this->checkPeriod($model_id, $date, $type, $summary_id, $range);
            }
            else{
                $result = $this->registerAttendance($model_id, $date, $type, $subType, $summary_id, $range, $from, $to, $minutes, $minuteTypes, $text);
            }
        }
        else{
            $result = $this->makeupTime($model_id, $date, $type, $subType, $range, $from, $to, $minutes, $text);
        }

        return response()->json($result);
    }

    protected function registerAttendance($model_id, $date, $type, $subtype, $summary_id, $range, $from, $to, $minutes, $minuteTypes, $text){
        $msg_type = "";
        $comment = "";
        $msg = "";
        $icon = "";
        $code = "";
        $name = auth()->user()->first_name." ".auth()->user()->last_name;
        $user_id = auth()->user()->id;
        $attendance = NULL;
        $has_at = NULL;
        
        $summary = AttendanceSummary::where([['model_id', '=', $model_id], ['range', '=', $range]])->first();
        $model = User::where('id', $model_id)->first();
        if ($minuteTypes === 2){
            $minuteTypes = "(".($minutes/2).") Dobles";
        }else{
            $minuteTypes = "(".($minutes).") Simples";
        }

        try {
            DB::beginTransaction();

            if ($type === 1 ){
                $msg_type = "Se registró la asistencia correctamente";
                $has_at = Attendance::where('model_id', $model_id)->where('date', $date)->first();
                if ($subtype === 1){
                    $comment = $name. " registra que la modelo " .$model->nick. " llega puntual";
                    if (!is_null($summary)) {
                        $worked = $summary->worked_days;
                        $summary->update([
                            'worked_days' => $worked + 1,
                            'setting_location_id' => $model->setting_location_id
                        ]);
                    }
                }
                else{
                    $comment = $name. " registra que la modelo " .$model->nick. " debió llegar a las " .$from. " pero llegó a las " .$to. " con " .$minutes. " minutos de retraso / ".$minuteTypes;
                    $totalMinutes = $summary->total_minutes;
                    $worked = $summary->worked_days;
                    $summary->update([
                        'total_minutes' => $totalMinutes + $minutes,
                        'worked_days' => $worked + 1,
                        'setting_location_id' => $model->setting_location_id
                    ]);
                }

                $statexists = Statistics::where([['user_id', '=', $model_id], ['date', Carbon::now()->format('Y-m-d')]])->first();
                $monitoring_exists = Monitoring::where('model_id', $model_id)->where('date', Carbon::now()->format('Y-m-d'))->exists();
                if (!is_null($statexists)){
                    if (!is_null($statexists->value) && $monitoring_exists === false){
                       Monitoring::updateOrCreate([
                           'model_id' => $model_id,
                           'range' => $range,
                           'date' => $date,
                           'status' => 0,
                           'setting_location_id' => $model->setting_location_id,
                       ]);
                    }
                }
            }
            elseif ($type === 5){
                if ($subtype === 4) {
                    $comment = $name. " registra que la modelo " .$model->nick. " se encuentra enferma para la fecha ".$date;
                    $type = 4;
                }
                else{
                    $comment = $name. " reporta que " .$text;
                }

                $msg_type = "Se registró la falta justificada correctamente";

                if (!is_null($summary)){
                    $justified = $summary->justified_days;
                    $summary->update([
                        'justified_days' => $justified + 1,
                        'setting_location_id' => $model->setting_location_id
                    ]);
                }
            }
            elseif ($type === 6){
                $m = Attendance::where('date', $date)->where('model_id', $model_id)->exists();
                if ($m){
                    $msg_type = "Ya ha marcado inasistencia a esta modelo el dia de hoy!";
                    $code = 200;
                    $icon = "error";
                }else{
                    $schedule = Schedule::where('user_id', $model_id)->first();
                    if (!is_null($schedule))
                    {
                        $model_schedule = ScheduleSessions::where('session', $schedule->session)->first();
                        $minutes = $model_schedule->working_time;
                    }
                    else{
                        $minutes = 480;
                    }

                    $comment = $name. " registra que la modelo " .$model->nick. " no cumplió su jornada laboral, se añadieron " .$minutes. " minutos";
                    $msg_type = "Se registró la falta injustificada correctamente";

                    if (!is_null($summary)){
                        $totalMinutes = $summary->total_minutes;
                        $unjustified = $summary->unjustified_days;
                        $summary->update([
                            'total_minutes' => $totalMinutes + $minutes,
                            'unjustified_days' => $unjustified + 1,
                            'setting_location_id' => $model->setting_location_id
                        ]);
                    }
                }
            }
            elseif ($type === 7){
                $comment = $name. " registra que la modelo " .$model->nick. " se desconecto antes de tiempo. Se añadieron ".$minutes."minutos / ".$minuteTypes;
            }

            if (!is_null($has_at)){
                $has_at->update([
                    'attendance_type' => $type,
                ]);
            }else{
                $attendance = Attendance::updateOrCreate([
                    'attendance_summary_id' => $summary_id,
                    'model_id' => $model_id,
                    'setting_location_id' => $model->setting_location_id,
                    'range' => $range,
                    'date' => $date,
                    'attendance_type' => $type,
                    'attendance_minutes' => $minutes,
                    'created_by' => $user_id,
                ]);
            }

            AttendanceComment::updateOrCreate([
                'attendance_id' => $attendance->id,
                'attendance_status_id' => $type,
                'created_by' => $user_id,
                'comment' => $comment
            ]);

            if ($attendance){
                $msg = $msg_type;
                $code = 200;
                $icon = "success";
            }
            DB::commit();
        }
        catch (\Exception $ex){
            $msg = "Se ha presentado un error. Comuniquese con el admin".$ex->getMessage();
            $code = 500;
            $icon = "error";
            DB::rollBack();
        }

        return [
            'data' => $attendance,
            'msg' => $msg,
            'icon' => $icon,
            'code' => $code
        ];
    }

    protected function checkPeriod($model_id, $date, $attendance_type, $attendance_summary_id, $range)
    {
        $period_date = Carbon::parse($date)->subDays(23)->format('Y-m-d');
        $date_to = Carbon::parse($date)->subDays(2)->format('Y-m-d');
        $created_by = auth()->user()->setting_role_id;
        $msg = "";
        $icon = "";
        $code = "";

        $periods = Attendance::where([['model_id', $model_id], ['attendance_type', $attendance_type]])->whereBetween('date', [$period_date, $date_to])->count();
        $model = User::where('id', $model_id)->first();
        if ($periods !== 0) {
//            $model = User::where('id', $model_id)->first();
            $last_period = Attendance::where([['model_id', $model_id], ['attendance_type', $attendance_type]])->whereBetween('date', [$period_date, $date_to])->latest('date')->first();

            $last_period_to_date = $last_period->date;
            $now = Carbon::now()->format('Y-m-d');
            $start_date = Carbon::createFromFormat('Y-m-d', $last_period_to_date);
            $end_date = Carbon::createFromFormat('Y-m-d', $now);
            $difference_days = $start_date->diffInDays($end_date);
            $title = "Reporte de Periodo";
            $comment = " Se intentó reportar periodo a la Modelo ".$model->nick." sin embargo el sistema informa que su ultimo periodo fue solo hace ".$difference_days." dias";
            $this->createAttendanceTask($title, $comment, $model->nick, $model_id, $created_by);

            $exists = Attendance::where([['model_id', $model_id], ['date', $date]])->exists();

            if (!$exists) {
                $name = auth()->user()->first_name." ".auth()->user()->last_name;
                $attendance_type = 6;
                $schedule = Schedule::where('user_id', $model_id)->first();
                if (!is_null($schedule)){
                    $model_schedule = ScheduleSessions::where('session', $schedule->session)->first();
                    $totalMinutes = $model_schedule->working_time;
                }else{
                    $totalMinutes = 480;
                }
                $comment = $name. " intentó reportar periodo pero la modelo ya tiene sus dos dias de periodo, se insertó falta ".$totalMinutes. " minutos";

                try {
                    DB::beginTransaction();

                    $attendance = Attendance::create([
                        'attendance_summary_id' => $attendance_summary_id,
                        'model_id' => $model_id,
                        'setting_location_id' => $model->setting_location_id,
                        'range' => $range,
                        'date' => $date,
                        'attendance_type' => $attendance_type,
                        'attendance_minutes' => 480,
                        'created_by' => auth()->user()->id,
                    ]);

                    AttendanceComment::create([
                        'attendance_id' => $attendance->id,
                        'attendance_status_id' => $attendance_type,
                        'created_by' => auth()->user()->id,
                        'comment' => $comment
                    ]);

                    $attendance_summary = AttendanceSummary::findOrFail($attendance_summary_id);
                    $summary_total_mins = $attendance_summary->total_minutes;
                    $unjustified = $attendance_summary->unjustified_days;

                    $attendance_summary->update([
                        'total_minutes' => $totalMinutes +  $summary_total_mins,
                        'unjustified_days' => $unjustified + 1,
                        'setting_location_id' => $model->setting_location_id
                    ]);

                    $msg = "Se registró una inasistencia porque la modelo ya tiene todos sus dias de periodo";
                    $code = 200;
                    $icon = "success";

                    DB::commit();
                }
                catch(\Exception $ex){
                    $msg = "Se ha presentado un error. Comuniquese con el admin";
                    $code = 500;
                    $icon = "error";
                    DB::rollBack();
                }
            }
        }
        else {
            try {
                DB::beginTransaction();

                $attendance = Attendance::create([
                    'attendance_summary_id' => $attendance_summary_id,
                    'model_id' => $model_id,
                    'setting_location_id' => $model->setting_location_id,
                    'range' => $range,
                    'date' => $date,
                    'attendance_type' => $attendance_type,
                    'attendance_minutes' => 0,
                    'created_by' => auth()->user()->id,
                ]);

                $summary = AttendanceSummary::findOrFail($attendance_summary_id);

                if (!empty(auth()->user()->nick)){
                    $comment = auth()->user()->nick. " ha reportado periodo";
                }
                else{
                    $comment = auth()->user()->first_name. " ". auth()->user()->last_name. " reporta periodo para la modelo " .$model->nick;
                }

                AttendanceComment::create([
                    'attendance_id' => $attendance->id,
                    'attendance_status_id' => $attendance_type,
                    'created_by' => auth()->user()->id,
                    'comment' => $comment
                ]);

                if (!is_null($summary)){
                    $period = $summary->period;
                    $summary->update([
                        'period' => $period + 1,
                        'setting_location_id' => $model->setting_location_id
                    ]);
                }

                $msg = "Se registró el periodo correctamente";
                $code = 200;
                $icon = "success";

                DB::commit();
            }
            catch (\Exception $ex){
                $msg = "Se ha presentado un error. Comuniquese con el admin ".$ex->getMessage();
                $code = 500;
                $icon = "error";

                DB::rollBack();
            }
        }

        return [
            'msg' => $msg,
            'code' => $code,
            'icon' => $icon
        ];
    }

    protected function makeupTime($model_id, $date, $type, $subtype, $range, $from, $to, $minutes, $text){
        $comment = NULL;
        $msg = NULL;
        $code = NULL;
        $icon = NULL;
        $attendance = NULL;
        $name = auth()->user()->first_name." ".auth()->user()->last_name;
        $user_id = auth()->user()->id;

        if (empty($date)){
            $msg = "Escoja una fecha del tiempo que desea recuperar recuperar!";
            $code = 404;
            $icon = "error";

            return [
                'msg' => $msg,
                'icon' => $icon,
                'code' => $code
            ];
        }

        $model = User::where('id', $model_id)->first();
        $summary = AttendanceSummary::where('model_id', '=', $model_id)->where('range', '=', $range)->first();
//        dd($summary);
        if (!is_null($summary)) {
            $totalSummaryMinutes = $summary->total_minutes;
            $totalSummaryRecoveredMinutes = $summary->total_recovery_minutes;
            $worked_days = $summary->worked_days + 1;
            $period_days = $summary->period + 1;
            $justified_days = $summary->justified_days + 1;
            $unjustified_days = $summary->unjustified_days + 1;
            $summary_id = $summary->id;
        }
        else {
            $msg = "La modelo no tiene sumario de asistencia!";
            $code = 404;
            $icon = "error";

            return [
                'msg' => $msg,
                'icon' => $icon,
                'code' => $code
            ];
        }

        $schedule = Schedule::where('user_id', $model_id)->first();
        $model_schedule = ScheduleSessions::where('session', $schedule->session)->first();
        if (!is_null($model_schedule)){
            $working_minutes = $model_schedule->working_time;
        }
        else{
            $working_minutes = 480;
        }

        try {
            DB::beginTransaction();
            if ($minutes > $working_minutes)
            {
                $msg = "No puedo recuperar mas de ".$working_minutes." minutes";
                $code = 403;
                $icon = "error";

                return response()->json([
                    'msg' => $msg,
                    'icon' => $icon,
                    'code' => $code
                ]);
            }

            $attendance = Attendance::where('model_id', '=', $model_id)->where('date', '=', $date)->first();

            if (is_null($attendance)){
                if($type === 11)
                {
                    $comment = $name. ": La modelo ".$model->nick." trabaja dia libre desde".$from." hasta ".$to." / ".$minutes." minutos recuperados";
                }
                elseif ($type === 12)
                {
                    $comment = $name. ": La modelo ".$model->nick." hace tiempo extra desde ".$from." hasta ".$to." / ".$minutes." minutos recuperados";
                }
                elseif ($type === 13)
                {
                    $comment = $name. ": La modelo ".$model->nick." hace doble turno ".$minutes." minutos recuperados";
                }

                // add extra mins of the same
                $recovered_mins_today = Attendance::where('model_id', $model_id)->where('date', $date)->first();
                if(!is_null($recovered_mins_today)){
                    $minutes = $recovered_mins_today + $minutes;
                }

                // store attendance
                $attendance = Attendance::create([
                    'attendance_summary_id' => $summary_id,
                    'model_id' => $model_id,
                    'setting_location_id' => $model->setting_location_id,
                    'range' => $range,
                    'date' => $date,
                    'attendance_type' => $type,
                    'attendance_minutes' => 0,
                    'recovery_minutes' => $minutes,
                    'created_by' => $user_id,
                ]);

                AttendanceComment::create([
                    'attendance_id' => $attendance->id,
                    'attendance_status_id' => $type,
                    'created_by' => $user_id,
                    'comment' => $comment
                ]);

                // update summary
                $totalTime = $totalSummaryMinutes - $minutes;
                if ($totalTime <= 0){
                    $totalTime = 0;
                }

                $summary->update([
                    'total_minutes' => $totalTime,
                    'total_recovery_minutes' => $totalSummaryRecoveredMinutes + $minutes,
                    'setting_location_id' => $model->setting_location_id
                ]);
            }
            else{
                $recoveredMinutes = $attendance->recovery_minutes + $minutes;
                $totalTimeRecovered = $totalSummaryRecoveredMinutes + $minutes;
                $totalTimeRemaining = $totalSummaryMinutes - $minutes;
                if ($totalTimeRemaining <= 0){
                    $totalTimeRemaining = 0;
                }

                //error cometido
                if ($type === 14){
                    $comment = "Error cometido: " .$text. " / ".$minutes." minutos recuperados por ".$name;

                    $attendance->update([
                        'recovery_minutes' => $recoveredMinutes,
                        'updated_by' => $user_id
                    ]);

                    $summary->update([
                        'total_minutes' => $totalTimeRemaining,
                        'total_recovery_minutes' => $totalTimeRecovered
                    ]);
                }

                //remover falta injustificada
                if($type === 15){
                    $totalTimeRemaining = $totalSummaryMinutes - $working_minutes;
                    if ($totalTimeRemaining <= 0){
                        $totalTimeRemaining = 0;
                    }

                    //por periodo
                    if ($subtype === 3){
                        $comment = $name. " removió falta injustificada de " .$date. " para " .$model->nick. " por periodo. Se removieron " .$working_minutes. " minutos";
                        $type = 3;

                        $summary->update([
                            'total_minutes' => $totalTimeRemaining,
                            'period' => $period_days
                        ]);
                    }

                    //por enferma
                    if ($subtype === 4){
                        $comment = $name. " removio falta injustificada de " .$date. " para " .$model->nick. " por falta justificada, modelo se encuentra enferma. Se removieron " .$working_minutes. " minutos";
                        $type = 4;

                        $summary->update([
                            'total_minutes' => $totalTimeRemaining,
                            'justified_days' => $justified_days
                        ]);
                    }

                    //por falta justificada
                    if ($subtype === 15){
                        $comment = $name. " removio falta injustificada de " .$date. " para " .$model->nick. " por falta justificada: ".$text;
                        $type = 5;

                        $summary->update([
                            'total_minutes' => $totalTimeRemaining,
                            'justified_days' => $justified_days
                        ]);
                    }

                    if ($subtype === 16){
                        $comment = $name. " removio falta injustificada de " .$date. " para " .$model->nick. " por falta justificada: ".$text;
                        $type = 5;

                        $summary->update([
                            'total_minutes' => $totalTimeRemaining,
                            'justified_days' => $justified_days
                        ]);
                    }

                    //trabaja en horario diferente
                    if ($subtype === 17){
                        $comment = $name. " removio falta injustificada de " .$date. " para " .$model->nick. " porque la modelo asiste en horario diferente";
                        $type = 1;
                        if ($unjustified_days <= 0){
                            $unjustified_days = 0;
                        }
                        $summary->update([
                            'total_minutes' => $totalTimeRemaining ,
                            'worked_days' => $worked_days,
                            'unjustfied_days' => $unjustified_days
                        ]);
                    }

                    $attendance = Attendance::where('model_id', '=', $model_id)->where('date', '=', $date)->where('attendance_type', '=', 6)->first();

                    $attendance->update([
                        'attendance_type' => $type,
                        'attendance_minutes' => 0,
                        'updated_by' => $user_id,
                    ]);
                }

                //trabaja dia libre
                if($type === 11)
                {
                    $comment = $name. ": La modelo ".$model->nick." trabaja dia libre desde".$from." hasta ".$to." / ".$minutes." recuperados";
                    $attendance->update([
                        'recovery_minutes' => $recoveredMinutes,
                        'updated_by' => $user_id
                    ]);

                    $summary->update([
                        'worked_days' => $worked_days + 1,
                        'total_minutes' => $totalTimeRemaining,
                        'total_recovery_minutes' => $totalTimeRecovered
                    ]);
                }

                //hace tiempo extra
                if ($type === 12)
                {
                    $comment = $name. ": La modelo ".$model->nick." hace tiempo extra desde ".$from." hasta ".$to." / ".$minutes." recuperados";
                    $attendance->update([
                        'recovery_minutes' => $recoveredMinutes,
                        'updated_by' => $user_id
                    ]);

                    $summary->update([
                        'total_minutes' => $totalTimeRemaining,
                        'total_recovery_minutes' => $totalTimeRecovered
                    ]);
                }

                //hace doble turno
                if ($type === 13)
                {
                    $comment = $name. ": La modelo ".$model->nick." hace doble turno ".$minutes." recuperados";
                    $attendance->update([
                        'recovery_minutes' => $recoveredMinutes,
                        'updated_by' => $user_id
                    ]);

                    $summary->update([
                        'total_minutes' => $totalTimeRemaining,
                        'total_recovery_minutes' => $totalTimeRecovered
                    ]);
                }

                AttendanceComment::create([
                    'attendance_id' => $attendance->id,
                    'attendance_status_id' => $type,
                    'created_by' => $user_id,
                    'comment' => $comment
                ]);
            }

            if ($attendance){
                $msg = "Se ha realizado el cambio de asistencia correctamente!";
                $code = 200;
                $icon = "success";
            }

            DB::commit();
        }
        catch (\Exception $ex){
            $msg = "Se ha presentado un error. Comuniquese con el admin".$ex->getMessage();
            $code = 500;
            $icon = "error";
            DB::rollBack();
        }

        return [
            'data' => $attendance,
            'msg' => $msg,
            'icon' => $icon,
            'code' => $code
        ];
    }

    protected function createAttendanceTask($title, $comment, $user, $model_id, $created_by)
    {
        $created_by_type = 0; // Role
        $status = 0;
        $terminated_by = 0;

        $task_controller = new TaskController();
        $code = $task_controller->generateCode();

        $current_date = Carbon::now()->format('Y-m-d');
        $should_finish = Carbon::parse($current_date)->addDay(1);

        $receivers = [
            'to_roles' => [
                ['id' => 1, 'name' => 'Gerente'],
                ['id' => 7, 'name' => 'Recursos Humanos'],
                ['id' => 11, 'name' => 'Programador'],
                ['id' => 35, 'name' => 'Recursos Humanos Operativo'],
            ],
            'to_users' => [
                ['id' => $model_id, 'name' => $user]
            ],
            'to_models' => [],
        ];

        $task = Task::create([
            'created_by_type' => $created_by_type,
            'created_by' =>  $created_by,
            'title' => $title,
            'status' => $status,
            'should_finish' => $should_finish,
            'terminated_by' => $terminated_by,
            'code' => $code
        ]);

        $request_object = new Request(['receivers' => json_encode($receivers), 'task_id' => $task->id, 'from_add_receivers' => 0]);
        (new TaskController)->addReceivers($request_object);

        $task_comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => auth()->user()->id,
            'comment' => $comment
        ]);

        return response()->json([
            'task' => $task,
            'comment' => $task_comment
        ]);
    }

    public function searchUnjustifiedDate(Request $request)
    {
        $attendance = Attendance::where([
            ['model_id', '=', $request->get('model_id')],
            ['range', '=', $request->get('range')],
            ['attendance_type', '=', 6]
        ])->get();

        return response()->json($attendance);
    }

    public function saveConnection(Request $request)
    {
        $model_id = $request->get('model_id');
        $date = $request->get('date');
        $range = $request->get('range');
        $type = $request->get('type');
        $from = $request->input('from');
        $to = $request->input('to');
        $minutes = $request->get('minutes');
        $minuteTypes = $request->get('minutes_type');
        $msg = NULL;
        $code = NULL;
        $icon = NULL;
        $comment = NULL;
        $name = auth()->user()->first_name. " ". auth()->user()->last_name;
        $user_id = auth()->user()->id;

        $attendance = Attendance::where([['model_id', $model_id], ['date', $date]])->first();
        $model = User::select('nick')->where('id', $model_id)->first();
        if ($minuteTypes === 2){
            $minuteTypes = "(".($minutes/2).") minutos Dobles";
        }else{
            $minuteTypes = "(".($minutes).") minutos Simples";
        }

        if (is_null($attendance)){
            $msg = "La modelo no tienen asistencia aun";
            $code = 404;
            $icon = "Error";
        }
        else{
            $summary = AttendanceSummary::where([['model_id', $model_id], ['range', $range]])->first();
            $attendance_minutes = $attendance->attendance_minutes + $minutes;
            $summary_minutes = $summary->total_minutes + $minutes;

            try {
                DB::beginTransaction();

                if ($type === 1){
                    $comment = $name.  " registra que la modelo " .$model->nick. " se connecta puntual";
                    $type = 2;
                }
                elseif ($type === 2){
                    $comment = $name.  " registra que la modelo " .$model->nick. " debió conectarse a las " .$from. " y se connecto a las " .$to. " con un retraso de " .$minutes. " minutos  / ".$minuteTypes;
                    $type = 2;
                }

                AttendanceComment::create([
                    'attendance_id' => $attendance->id,
                    'attendance_status_id' => $type,
                    'created_by' => $user_id,
                    'comment' => $comment
                ]);

                $attendance->update([
                    'attendance_minutes' => $attendance_minutes
                ]);

                $summary->update([
                    'total_minutes' => $summary_minutes
                ]);

                DB::commit();

                if ($attendance){
                    $msg = "Asistencia actualizada correctamente";
                    $code = 200;
                    $icon = "success";
                }

            }catch (\Exception $ex){
                $msg = "Ha ocurrido un problema, comuniquese con el admin";
                $code = 500;
                $icon = "error";

                DB::rollBack();
            }
        }

        return response()->json([
            'data' => $attendance,
            'msg' => $msg,
            'code' => $code,
            'icon' => $icon
        ]);
    }

    public function pauseDisconnection(Request $request)
    {
        $model_id = $request->get('model_id');
        $date = $request->get('date');
        $range = $request->get('range');
        $type = $request->get('type');
        $subtype = $request->get('subtype');
        $from = $request->input('from');
        $to = $request->input('to');
        $minutes = $request->get('minutes');
        $minuteTypes = $request->get('minutes_type');
        $msg = NULL;
        $code = NULL;
        $icon = NULL;
        $comment = NULL;
        $name = auth()->user()->first_name." ".auth()->user()->last_name;

        $attendance = Attendance::where([['model_id', $model_id], ['date', $date]])->first();
        $model = User::where('id', $model_id)->first();
        if ($minuteTypes === 2){
            $minuteTypes = "(".($minutes/2).") minutos Dobles";
        }else{
            $minuteTypes = "(".($minutes).") minutos Simples";
        }

        if (is_null($attendance)){
            $msg = "La modelo no tienen asistencia aun";
            $code = 404;
            $icon = "Error";
        }
        else{
            $summary = AttendanceSummary::where([['model_id', $model_id], ['range', $range]])->first();
            $attendance_minutes = $attendance->attendance_minutes + $minutes;
            $summary_minutes = $summary->total_minutes + $minutes;

            try {
                DB::beginTransaction();

                if ($type === 1){
                    if ($subtype === 1){
                        $comment = $name. " registra que la modelo pausó por mas del tiempo permitido desde" .$from. " hasta " .$to. ", se añadieron ".$minutes. " minutos / " .$minuteTypes;
                    }else{
                        $type = 7;
                        $comment = $name. " registra que la modelo " .$model->nick. " se desconectó antes de tiempo, se añadieron ".$minutes. " minutos / ".$minuteTypes;
                    }
                }

                AttendanceComment::create([
                    'attendance_id' => $attendance->id,
                    'attendance_status_id' => $type,
                    'created_by' => auth()->user()->id,
                    'comment' => $comment
                ]);

                $attendance->update([
                    'attendance_minutes' => $attendance_minutes
                ]);

                $summary->update([
                    'total_minutes' => $summary_minutes
                ]);

                if ($attendance){
                    $msg = "Asistencia actualizada correctamente";
                    $code = 200;
                    $icon = "success";
                }
                DB::commit();
            }catch (\Exception $ex){
                $msg = "Ha ocurrido un problema, comuniquese con el admin".$ex->getMessage();
                $code = 500;
                $icon = "success";

                DB::rollBack();
            }
        }

        return response()->json([
            'msg' => $msg,
            'code' => $code,
            'icon' => $icon
        ]);
    }

    /*
        Reports
    */
    public function allReports(Request $request)
    {
        $location_id = $request->get('selectedLocation');
        $type = $request->get('selectedType');
        $range = $request->get('range');
        $dates = [];
        $data = [];

        $start_date = $request->get('start');
        $end_date = $request->get('end');

        if ($start_date == Carbon::now()->format('Y-m-d')){
            $week_start = Carbon::now()->startOfWeek(Carbon::SUNDAY)->toDateString();
            $week_end = Carbon::now()->endOfWeek(Carbon::SATURDAY)->toDateString();
        }else{
            $week_start = $start_date;
            $week_end = $end_date;
        }

        $from = Carbon::parse($week_start);
        $to = Carbon::parse($week_end);

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
        $data['weeks'] = $dates;

        $reports = Monitoring::select('model_id')->where([
            ['monitoring.setting_location_id', '=', $location_id],
            ['monitoring.range', '=', $range]
        ])->distinct('model_id')->get();

        foreach ($reports as $key => $report){
//            $model_nick = User::where([['id', '=', $report->model_id], ['status', 1]])->first();
            $model_nick = User::where('id', '=', $report->model_id)->first();
            if (!is_null($model_nick)){
                $data['reports'][$key]["nick"] = $model_nick->nick;
                $data['reports'][$key]["model_id"] = $report->model_id;
            }else{
                continue;
            }

            $dayCount = 0;
            $disabled = false;
            foreach($dates as $date) {
                $date_report = Monitoring::with('archives')->where([['model_id', '=', $report->model_id], ['date', '=', $date]])->first();
                $arrival = Attendance::join('attendance_comments', 'attendance_comments.attendance_id', '=', 'attendances.id')->where([['model_id', '=', $report->model_id], ['date', '=', $date]])->first();
                $total = Statistics::where([['user_id', '=', $report->model_id], ['range', '=', $range]])->sum('value');
                if(is_null($total)) {
                    $total = 0;
                }
                $variant = "outline-secondary";
                $status = null;
                $monitor_name = null;
                if(!is_null($date_report)){
                    $monitoring_qualification = MonitoringQualification::with('images')->where('monitoring_id', '=', $date_report->id)->first();
                    $monitor = User::where('id', $date_report->monitor_id)->first();
                    if (!is_null($monitor)){
                        $monitor_name = $monitor->first_name." ".$monitor->last_name;
                    }

                    $status = $date_report->status;
                    if ($status == 1)
                    {
                        $variant = "outline-warning";
                    }
                    elseif ($status == 2)
                    {
                        $variant = "outline-success";
                    }
                    elseif ($status == 0)
                    {
                        $variant = "outline-danger";
                    }
                }
                else{
                    $monitoring_qualification = null;
                    $variant = "outline-secondary";
                }

                if(!is_null($arrival)) {
                    if ($arrival->attendance_type == 3 || $arrival->attendance_type == 4 || $arrival->attendance_type == 9 || $arrival->attendance_type == 8) {
                        $disabled = true;
                    }
                }

                $data['reports'][$key][strtolower(Carbon::parse($dates[$dayCount])->format('l'))] = [
                    'report_id' => is_null($date_report) ? 0 : $date_report->id,
                    'monitor_id' => is_null($date_report) ? 0 : $date_report->monitor_id,
                    'monitor_name' => $monitor_name,
                    'arrival' => is_null($arrival) ? 0 : ($arrival->attendance_type === 1 ? 1 : 2),
                    'connection' => is_null($arrival) ? 0 : ($arrival->attendance_status_id === 1 ? 1 : 2),
                    'date' => $date,
                    'status' => $status,
                    'variant' => $variant,
                    'day' => strtolower(Carbon::parse($dates[$dayCount])->format('l')),
                    'report' => $monitoring_qualification,
                    'setting_location_id' => is_null($date_report) ? 0 : $date_report->setting_location_id,
                    'step' => is_null($date_report) ? 0 : $date_report->step,
                    'archives' => is_null($date_report) ? 0 : $date_report->archives,
                    'attendance' => $arrival,
                    'disabled' => $disabled,
                    'total' => $total,
                ];
                $dayCount ++;
            }
        }

        /*header('Content-Type: application/json');
        echo json_encode($data);*/
        return response()->json(collect($data));
    }

    protected function assignReport(Request $request)
    {
        $date = $request->get('date');
        $monitor_id = $request->get('monitor_id');
        $status = $request->get('status');
        $report_id = $request->get('monitoring_id');
        $msg = "";
        $icon = "";
        $code = "";
        $updated_status = "";

        try
        {
            DB::beginTransaction();
            $report = Monitoring::findOrFail($report_id);
            $update = $report->update([
                'monitor_id' => $monitor_id,
                'assigned_by' => auth()->user()->id,
                'date' => $date,
                'status' => $status,
            ]);

            if ($update){
                $user = User::where('id', '=', $monitor_id)->first();
                $msg = "Reporte asignado a ".$user->first_name." ".$user->last_name;
                $code = 200;
                $icon = "success";
                $updated_status = $report->status;
            }
            DB::commit();
        }
        catch (\Exception $ex)
        {
            $msg = "Ha ocurrido un error, por favor, comunicarse con el admin";
            $code = 500;
            $icon = "error";
            DB::rollBack();
        }

        return response()->json([
            'msg' => $msg,
            'code' => $code,
            'icon' => $icon,
            'status' => $updated_status,
        ]);

    }

    protected function archiveReport(Request $request)
    {
        $msg = "";
        $icon = "";
        $code = "";

        try
        {
            DB::beginTransaction();
            $archive = MonitoringArchives::updateOrCreate([
                'user_id' => auth()->user()->id,
                'monitoring_id' => $request->get('monitoring_id'),
            ]);

            if ($archive){
                $msg = "Reporte archivado";
                $code = 200;
                $icon = "success";
            }
            DB::commit();
        }
        catch (\Exception $ex)
        {
            $msg = "Ha ocurrido un error, por favor, comunicarse con el admin".$ex;
            $code = 500;
            $icon = "error";
            DB::rollBack();
        }

        return response()->json([
            'msg' => $msg,
            'code' => $code,
            'icon' => $icon,
        ]);

    }

    public function finalizeReport(Request $request)
    {
        $report = Monitoring::findOrFail($request->get('monitoring_id'));
        $msg = "";
        $code = null;
        $icon = null;
        
        try
        {
            DB::beginTransaction();
            MonitoringQualification::create($request->all());

            if ($request->get('finalize')){
                $status = 2;
                $stepper = 3;
                $report->update([
                    'status' => $status,
                    'step' => $stepper
                ]);
            }
            DB::commit();

            $msg = "Finalizado correctamente";
            $code = 200;
            $icon = "success";
        }
        catch (\Exception $ex)
        {
            $msg = "Ha ocurrido un error, por favor, comunicarse con el admin";
            $code = 500;
            $icon = "error";

            DB::rollBack();

            return response()->json([
                'msg' => $msg,
                'icon' => $icon,
                'code' => $code,
            ]);
        }

        return response()->json([
            'msg' => $msg,
            'icon' => $icon,
            'code' => $code,
        ]);

    }

    protected function saveStep(Request $request)
    {
        $monitoring = MonitoringQualification::where('monitoring_id', '=', $request->get('monitoring_id'))->first();
        $report = Monitoring::findOrFail($request->get('monitoring_id'));

        if (is_null($monitoring)){
            try
            {
                DB::beginTransaction();
                $step = MonitoringQualification::create($request->all());

                if ($request->get('finalize')){
                    $report->update([
                        'status' => $status = 2,
                        'step' => $stepper = 3
                    ]);
                }
                DB::commit();
            }
            catch (\Exception $ex)
            {
                $msg = "Ha ocurrido un error, por favor, comunicarse con el admin";
                $code = 500;
                $icon = "error";

                DB::rollBack();

                return response()->json([
                    'msg' => $msg,
                    'icon' => $icon,
                    'code' => $code,
                ]);
            }

            if ($step){
                $msg = "la informacion ha sido guardada";
                $icon = "success";
                $code = 200;

                $report = Monitoring::findOrFail($request->get('monitoring_id'));
                $report->update([
                    'step'=> $request->step
                ]);

                return response()->json([
                    'msg' => $msg,
                    'icon' => $icon,
                    'code' => $code,
                ]);
            }
        }
        else{
            try {
                DB::beginTransaction();

                if ($request->step == 0){
                    $step = $monitoring->update([
                        'look' => $request->input('look'),
                        'hairstyle' => $request->input('hairstyle'),
                        'makeup' => $request->input('makeup'),
                        'lingerie' => $request->input('lingerie'),
                        'manicure_pedicure' => $request->input('manicure_pedicure'),
                        'comment_on_general' => $request->input('comment_on_general'),
                    ]);

                    $status =  1;
                    $stepper = 0;
                }
                elseif ($request->step == 1){
                    $step = $monitoring->update([
                        'smiles' => $request->input('smiles'),
                        'visual_contact' => $request->input('visual_contact'),
                        'posture' => $request->input('posture'),
                        'lures_users' => $request->input('lures_users'),
                        'highlights_attributes' => $request->input('highlights_attributes'),
                        'hide_flaws' => $request->input('hide_flaws'),
                        'takes_recommendations' => $request->input('takes_recommendations'),
                        'interacts_online' => $request->input('interacts_online'),
                        'fulfills_user_wishes' => $request->input('fulfills_user_wishes'),
                        'uses_mic' => $request->input('uses_mic'),
                        'comment_on_show' => $request->input('comment_on_show'),
                    ]);

                    $status =  1;
                    $stepper = 1;
                }
                elseif ($request->step == 2){
                    $step = $monitoring->update([
                        'room_equipment' => $request->input('room_equipment'),
                        'room_lighting' => $request->input('room_lighting'),
                        'room_cleanliness' => $request->input('room_cleanliness'),
                        'camera' => $request->input('camera'),
                        'audio' => $request->input('audio'),
                        'music' => $request->input('music'),
                        'setting_location_id' => $request->input('setting_location_id'),
                        'room_number' => $request->input('room_number'),
                        'comment_on_room' => $request->input('comment_on_room')
                    ]);

                    $status =  1;
                    $stepper =  2;

                }
                else{
                    $step = $monitoring->update([
                        'comment_on_model' => $request->input('comment_on_model'),
                        'show_score' => $request->input('show_score'),
                        'comment_on_score' => $request->input('comment_on_score'),
                        'recommendations' => $request->input('recommendations'),
                        'room_status' => $request->input('room_status'),
                        'comment_room_status' => $request->input('comment_room_status'),
                    ]);

                    $status =  1;
                    $stepper = 3;
                }

                DB::commit();

                if ($step){
                    $msg = "Informacion general ha sido guardada";
                    $icon = "success";
                    $code = 200;

                    if ($request->get('finalize')){
                        $status = 2;
                        $stepper = 3;
                    }

                    $report->update([
                        'step'=> $stepper,
                        'status' => $status
                    ]);

                    return response()->json([
                        'msg' => $msg,
                        'icon' => $icon,
                        'code' => $code,
                    ]);
                }
            }
            catch (\Exception $ex){

                $msg = "Ha ocurrido un error, por favor, comunicarse con el admin";
                $code = 500;
                $icon = "error";

                DB::rollBack();

                return response()->json([
                    'msg' => $msg,
                    'icon' => $icon,
                    'code' => $code,
                ]);
            }
        }
    }

    protected function saveReportImages(Request $request)
    {
        $monitoring = MonitoringQualification::where('monitoring_id', '=', $request->get('monitoring_id'))->first();

        if($request->file('report_images'))
        {
            $images = $request->file('report_images');
            $path = "GB/reports/images/";

            if(!Storage::disk('public')->exists($path)) {
                Storage::disk('public')->makeDirectory($path);
            }

            foreach ($images as $key => $image){
                $report_image = 'report'.$key.time().'.'.$image->getClientOriginalExtension();
                $resizedImage = Image::make($image)->resize(1500, 1000)->stream();
                Storage::disk('public')->put("GB/reports/images/".$report_image, $resizedImage);

                try {
                    DB::beginTransaction();
                    MonitoringImages::create([
                        'monitoring_qualification_id' => $monitoring->id,
                        'report_image' => $report_image
                    ]);
                    DB::commit();
                }
                catch(\Exception $ex){
                    DB::rollBack();
                }

            }
        }

        return response()->json(['msg' => 'Imagen guardada']);
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $qualification = MonitoringQualification::where('monitoring_id', '=', $id)->first();
            $images = MonitoringImages::where('monitoring_qualification_id', '=', $qualification->id)->get();
            foreach ($images as $image){
                Storage::disk('public')->delete('GB/reports/images/'.$image->report_image);
            }
            $qualification->delete();

            $msg = "Eliminado correctamente";
            $icon = "success";
            $code = 200;

            DB::commit();

            return response()->json([
                'msg' => $msg,
                'icon' => $icon,
                'code' => $code,
            ]);
        }
        catch(\Exception $ex)
        {
            $msg = "Ha ocurrido un error. Por favor, comuniquese con el admin".$ex;
            $icon = "error";
            $code = 500;

            DB::rollBack();

            return response()->json([
                'msg' => $msg,
                'icon' => $icon,
                'code' => $code,
            ]);
        }
    }

    public function getMonitors($location_id){

        if ($location_id === 1){
            $monitors = User::select('id','first_name', 'last_name')->whereIn('setting_role_id', [1, 6, 11])->where('status', '=', 1)->get();

            $all_monitors = User::select('id','first_name', 'last_name')->where([
                ['setting_role_id', '=', 6],
                ['setting_location_id', '=', 1],
                ['status', '=', 1],
            ])->get();

            $merged = $monitors->merge($all_monitors);
        }else{
            $monitors = User::select('id','first_name', 'last_name')->where([
                ['setting_role_id', '=', 6],
                ['setting_location_id', '=', $location_id],
                ['status', '=', 1],
            ])->get();

            $all_monitors = User::select('id','first_name', 'last_name')->where([
                ['setting_role_id', '=', 6],
                ['setting_location_id', '=', 1],
                ['status', '=', 1],
            ])->get();

            $merged = $monitors->merge($all_monitors);
        }

        return response()->json($merged);
    }

    /*
     * General summary
     */
    public function summary(Request $request)
    {
        $model_id = $request->input("model_id");

        if ($request->has('range')){
            $range = $request->get('range');
            $range = explode("/", $range);
            $from = $range[0];
            $to = $range[1];
        }else{
            $week_start = Carbon::now()->startOfWeek(Carbon::SUNDAY)->toDateString();
            $week_end = Carbon::now()->endOfWeek(Carbon::SATURDAY)->toDateString();
            $from = Carbon::parse($week_start)->format('Y-m-d');
            $to = Carbon::parse($week_end)->format('Y-m-d');
        }

        $summary = AttendanceSummary::join('users', 'users.id', 'attendance_summary.model_id')
            ->select('users.nick', 'attendance_summary.*')
            ->where('model_id', $model_id)
            ->whereBetween('date', [$from, $to])
            ->first();

        return response()->json($summary);
    }

    public function execute()
    {

//        $min_id = 31000;
        $min_id = 31000;
//        $max_id = 33200;
        $max_id = 33200;
        $reports = DB::connection('gbmedia')->table('reporte_monitoreo')
            ->whereBetween('id', [$min_id, $max_id])->get();
        $msg = "nothing bitch";
//        dd($reports);

        foreach ($reports as $report)
        {
            $status = 0;
            $step = NULL;
            if ($report->asignado === 'terminar') {
                $status = 2;
                $step = 3;
            }elseif($report === 'asignar'){
                $status->asignado = 1;
                $step = null;
            }

            $model = User::where('old_user_id', $report->id_modelo)
                ->where('status', 1)->first();
            $user = User::where('old_user_id', $report->id_monitora)->first();
            $from = Carbon::parse($report->fecha)->startOfWeek(Carbon::SUNDAY)->toDateString();
            $to = Carbon::parse($report->fecha)->endOfWeek(Carbon::SATURDAY)->toDateString();

            /*dump($user->id);
            continue;*/

            if (is_null($model)) {
                continue;
            }

            $monitoring = Monitoring::updateOrCreate([
                'model_id' => $model->id,
                'monitor_id' => is_null($user) ? NULL: $user->id,
                'assigned_by' => NULL,
                'range' => $from." / ".$to,
                'date' => $report->fecha,
                'status' => $status,
                'step' => $step,
                'setting_location_id' => $model->setting_location_id,
            ]);

//            $monitoring = Monitoring::where()->get();

            MonitoringQualification::updateOrCreate([
                'monitoring_id' => $monitoring->id,
                'look' => ($report->AG === 'bien') ? 1 : 2,
                'hairstyle' => ($report->PPT === 'bien') ? 1 : 2,
                'makeup' => ($report->MA === 'bien') ? 1 : 2,
                'lingerie' => ($report->V === 'bien') ? 1 : 2,
                'manicure_pedicure' => ($report->Unas ==='bien') ? 1 : 2,
                'comment_on_general' => is_null($report->AG_comentario) ? NULL : $report->AG_comentario,
                'smiles' => ($report->SC === 'si') ? 4 : 5,
                'visual_contact' => ($report->HC  === 'si') ? 4 : 5,
                'posture' => ($report->PA === 'si') ? 4 : 5,
                'lures_users' => ($report->CIP === 'si') ? 4 : 5,
                'highlights_attributes' => ($report->MSA === 'si') ? 4 : 5,
                'hide_flaws' => ($report->ESD === 'si') ? 4 : 5,
                'takes_recommendations' => ($report->AIM === 'si') ? 4 : 5,
                'interacts_online' => ($report->ICM === 'si') ? 4 : 5,
                'fulfills_user_wishes' => ($report->CDCPTV === 'si') ? 4 : 5,
                'uses_mic' => ($report->MHPM === 'si') ? 4 : 5,
                'comment_on_show' => is_null($report->durante_transm_comentario) ? NULL : $report->durante_transm_comentario,
                'room_equipment' => ($report->E === 'bien') ? 1 : 2,
                'room_lighting' => ($report->I === 'bien') ? 1 : 2,
                'room_cleanliness' => ($report->O === 'bien') ? 1 : 2,
                'camera' => ($report->C === 'bien') ? 1 : 2,
                'audio' => ($report->A === 'bien') ? 1 : 2,
                'music' => ($report->M === 'bien') ? 1 : 2,
                'setting_location_id' => $model->setting_location_id,
                'room_number' => empty($report->numero_cuarto) ? NULL : $report->numero_cuarto,
                'comment_on_room' => is_null($report->cuarto_comentario) ? NULL : $report->cuarto_comentario,
                'comment_on_model' => is_null($report->actitud_modelo_comentario) ? NULL : $report->actitud_modelo_comentario,
                'show_score' => $report->calif_show,
                'comment_on_score' => is_null($report->calif_show_comentario) ? NULL : $report->calif_show_comentario,
                'recommendations' => is_null($report->recomendaciones_modelo) ? NULL : $report->recomendaciones_modelo,
                'room_status' => ($report->SCE === 'divertido') ? 7 : 8,
                'comment_room_status' => is_null($report->SCE_comentario) ? NULL : $report->SCE_comentario,
            ]);

            $msg = "done bitch";
        }

        return response()->json($msg);
    }

    public function updateSatelliteUser()
    {
        /* $min_id = 685;
         $max_id = 2135;*/
        $owners = SatelliteOwner::select('id','user_id', 'owner')
            ->where('is_user', 1)
            ->where('user_id', 0)->get();
//            ->whereBetween('id', [$min_id, $max_id])->get();
        $msg = "nothing bitch";

//        dd($owners);
        foreach ($owners as $owner){
            /*if ($owner->user_id === 0){
                continue;
            }*/
            $model = User::where('nick', $owner->owner)->first();
            $accounts = SatelliteAccount::where('owner_id', $owner->id)->get();
            if (is_null($model)){
                continue;
            }
            foreach ($accounts as $account)
            {
                $user = User::where('old_user_id', $account->modified_by)->first();
                $account->update([
                    'user_id' => $model->id,
                    'modified_by' => is_null($user) ? NULL : $user->id
                ]);
            }

            $something = $owner->update([
                'user_id' => $model->id
            ]);

            if ($something){
                echo "<BR>".$owner->id;
            }else{
                echo "Error:".$owner->id;
            }
        }

        return response()->json($msg);
    }

    public function executeOne()
    {

        $min_id = 33200;
        $max_id = 34000;
        $reports = DB::connection('gbmedia')->table('reporte_monitoreo')->whereBetween('id', [$min_id, $max_id])->get();
        $msg = "nothing bitch";

        foreach ($reports as $report)
        {
            $status = 0;
            $step = NULL;
            if ($report->asignado === 'terminar') {
                $status = 2;
                $step = 3;
            }elseif($report === 'asignar'){
                $status->asignado = 1;
                $step = null;
            }

            $model = User::where('old_user_id', $report->id_modelo)
                ->where('status', 1)->first();
            $user = User::where('old_user_id', $report->id_monitora)->first();
            $from = Carbon::parse($report->fecha)->startOfWeek(Carbon::SUNDAY)->toDateString();
            $to = Carbon::parse($report->fecha)->endOfWeek(Carbon::SATURDAY)->toDateString();

            if (is_null($model)) {
                continue;
            }

            $monitoring = Monitoring::updateOrCreate([
                'model_id' => $model->id,
                'monitor_id' => is_null($user) ? NULL: $user->id,
                'assigned_by' => NULL,
                'range' => $from." / ".$to,
                'date' => $report->fecha,
                'status' => $status,
                'step' => $step,
                'setting_location_id' => $model->setting_location_id,
            ]);

            MonitoringQualification::updateOrCreate([
                'monitoring_id' => $monitoring->id,
                'look' => ($report->AG === 'bien') ? 1 : 2,
                'hairstyle' => ($report->PPT === 'bien') ? 1 : 2,
                'makeup' => ($report->MA === 'bien') ? 1 : 2,
                'lingerie' => ($report->V === 'bien') ? 1 : 2,
                'manicure_pedicure' => ($report->Unas ==='bien') ? 1 : 2,
                'comment_on_general' => is_null($report->AG_comentario) ? NULL : $report->AG_comentario,
                'smiles' => ($report->SC === 'si') ? 4 : 5,
                'visual_contact' => ($report->HC  === 'si') ? 4 : 5,
                'posture' => ($report->PA === 'si') ? 4 : 5,
                'lures_users' => ($report->CIP === 'si') ? 4 : 5,
                'highlights_attributes' => ($report->MSA === 'si') ? 4 : 5,
                'hide_flaws' => ($report->ESD === 'si') ? 4 : 5,
                'takes_recommendations' => ($report->AIM === 'si') ? 4 : 5,
                'interacts_online' => ($report->ICM === 'si') ? 4 : 5,
                'fulfills_user_wishes' => ($report->CDCPTV === 'si') ? 4 : 5,
                'uses_mic' => ($report->MHPM === 'si') ? 4 : 5,
                'comment_on_show' => is_null($report->durante_transm_comentario) ? NULL : $report->durante_transm_comentario,
                'room_equipment' => ($report->E === 'bien') ? 1 : 2,
                'room_lighting' => ($report->I === 'bien') ? 1 : 2,
                'room_cleanliness' => ($report->O === 'bien') ? 1 : 2,
                'camera' => ($report->C === 'bien') ? 1 : 2,
                'audio' => ($report->A === 'bien') ? 1 : 2,
                'music' => ($report->M === 'bien') ? 1 : 2,
                'setting_location_id' => $model->setting_location_id,
                'room_number' => empty($report->numero_cuarto) ? NULL : $report->numero_cuarto,
                'comment_on_room' => is_null($report->cuarto_comentario) ? NULL : $report->cuarto_comentario,
                'comment_on_model' => is_null($report->actitud_modelo_comentario) ? NULL : $report->actitud_modelo_comentario,
                'show_score' => $report->calif_show,
                'comment_on_score' => is_null($report->calif_show_comentario) ? NULL : $report->calif_show_comentario,
                'recommendations' => is_null($report->recomendaciones_modelo) ? NULL : $report->recomendaciones_modelo,
                'room_status' => ($report->SCE === 'divertido') ? 7 : 8,
                'comment_room_status' => is_null($report->SCE_comentario) ? NULL : $report->SCE_comentario,
            ]);

            $msg = "done bitch";
        }

        return response()->json($msg);
    }

    public function executeAddLocation()
    {
        $users = User::where('setting_role_id', 14)->where('status', 1)->get();
        $msg = "nothing inserted";
        $today = "2020-12-26";
        $at_from = Carbon::parse($today)->startOfWeek(Carbon::SUNDAY)->toDateString();
        $at_to = Carbon::parse($today)->endOfWeek(Carbon::SATURDAY)->toDateString();
        $attendance_range = $at_from." / ".$at_to;
        foreach($users as $user)
        {
            $attendance = Attendance::where('model_id', $user->id)->where('range', $attendance_range)->first();
            if (!is_null($attendance)){
                $attendance->update(['setting_location_id' => $user->setting_location_id]);
            }

            $summary = AttendanceSummary::where('model_id', $user->id)->where('range', $attendance_range)->first();
            if(!is_null($summary)){
                $summary->update(['setting_location_id' => $user->setting_location_id]);
            }
            $msg = "INSERTED";

        }

        return response()->json($msg);
    }

}
