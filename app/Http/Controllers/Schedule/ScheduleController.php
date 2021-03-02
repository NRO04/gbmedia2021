<?php

namespace App\Http\Controllers\Schedule;

use App\Http\Controllers\Controller;
use App\Models\Attendance\Attendance;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Schedule\Schedule;
use App\Models\Settings\SettingLocation;
use App\Models\Schedule\ScheduleSessions;
use App\Models\Schedule\ScheduleSessionTypes;
use App\Traits\TraitUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    use TraitUser;
    public function create()
    {
    	$locations = SettingLocation::where('id' , '!=' , 1)->get();
    	$sessions_type = ScheduleSessionTypes::all();
        $sessions = ScheduleSessions::orderBy('setting_location_id', 'asc')->get();
        return view('adminModules.schedule.create' ,
        	[
                'locations' => $locations,
                'sessions_type' => $sessions_type,
                'sessions' => $sessions,
            ]);
    }

    public function list()
    {
        return view('adminModules.schedule.list');
    }

    public function edit($loc, $ses)
    {
        $models = $this->getModelsLocation($loc);
        $schedules = Schedule::where('setting_location_id', '=', $loc)->where('session', '=', $ses)->get();
        $schedule_sessions = ScheduleSessions::where('setting_location_id', '=', $loc)->where('session', $ses)->get();
        return view('adminModules.schedule.edit' , [
            'location' => $loc,
            'ses' => $ses,
            'models' => $models,
            'schedules' => $schedules,
            'schedule_sessions' => $schedule_sessions,
        ]);
    }

    public function getModelsLocation($setting_location_id)
    {
        $models = $this->modelsLocationTrait($setting_location_id);
        return [
                'models' => $models,
            ];
    }

    public function store(Request $request)
    {
        $this->validate($request,
        [
            'location' => 'required',
            'sessions_type' => 'required',
            'available' => 'required',
        ],
        [
            'location' => 'Este campo es obligatorio',
            'sessions_type' => 'Este campo es obligatorio',
            'available' => 'Este campo es obligatorio',
        ]);

        try {
            DB::beginTransaction();

            $shift_start = '7:00 AM';
            $shift_end = '3:00 PM';
            $working_time = '480';
            $break = '45';
            if ($request->input('sessions_type') == 2 || $request->input('sessions_type') == 3)
                {
                    $shift_start = '3:00 PM';
                    $shift_end = '10:00 PM';
                    $working_time = '420';
                    $break = '30';
                }
            if ($request->input('sessions_type') == 4)
                {
                    $shift_start = '10:00 PM';
                    $shift_end = '6:00 AM';
                    $working_time = '480';
                    $break = '30';
                }

            $sessions_type = new ScheduleSessions();
            $sessions_type->session = $request->input('sessions_type');
            $sessions_type->setting_location_id = $request->input('location');
            $sessions_type->available = $request->input('available');
            $sessions_type->shift_start = $shift_start;
            $sessions_type->shift_end = $shift_end;
            $sessions_type->working_time = $working_time;
            $sessions_type->break = $break;
            $sessions_type->save();

            $models = $request->input('modelos');
            $cont = 0;
            $monday = $request->input('lunes');
            $tuesday = $request->input('martes');
            $wednesday = $request->input('miercoles');
            $thursday = $request->input('jueves');
            $friday = $request->input('viernes');
            $saturday = $request->input('sabado');
            $sunday = $request->input('domingo');
            foreach ($models as $model) {
                if ($model > 0) {
                    $schedule = new Schedule();
                    $schedule->user_id = $model;
                    $schedule->mon = $monday[$cont];
                    $schedule->tue = $tuesday[$cont];
                    $schedule->wed = $wednesday[$cont];
                    $schedule->thu = $thursday[$cont];
                    $schedule->fri = $friday[$cont];
                    $schedule->sat = $saturday[$cont];
                    $schedule->sun = $sunday[$cont];
                    $schedule->setting_location_id = $request->input('location');
                    $schedule->session = $request->input('sessions_type');
                    $schedule->save();
                }
                $cont++;
            }

            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function update(Request $request)
    {
        try {
            DB::beginTransaction();

            $setting_location_id = $request->input('location');
            $session_id = $request->input('session');
            $affectedRows = Schedule::where('setting_location_id', '=', $setting_location_id)->where('session', '=', $session_id)->delete();

            $models = $request->input('models');
            $cont = 0;
            $mon = $request->input('mon');
            $tue = $request->input('tue');
            $wed = $request->input('wed');
            $thu = $request->input('thu');
            $fri = $request->input('fri');
            $sat = $request->input('sat');
            $sun = $request->input('sun');
            foreach ($models as $model) {
                if ($model > 0) {
                    $schedule = new Schedule();
                    $schedule->user_id = $model;
                    $schedule->mon = $mon[$cont];
                    $schedule->tue = $tue[$cont];
                    $schedule->wed = $wed[$cont];
                    $schedule->thu = $thu[$cont];
                    $schedule->fri = $fri[$cont];
                    $schedule->sat = $sat[$cont];
                    $schedule->sun = $sun[$cont];
                    $schedule->setting_location_id = $setting_location_id;
                    $schedule->session = $session_id;
                    $schedule->save();

                    $week_start = Carbon::now()->startOfWeek(Carbon::SUNDAY)->toDateString();
                    $week_end = Carbon::now()->endOfWeek(Carbon::SATURDAY)->toDateString();
                    $rest_attendance = Attendance::where('model_id', $model)->where('range', $week_start." / ".$week_end)->first();
                    if (!is_null($rest_attendance)){
                        $rest_attendance->update([
                            'date' => Carbon::now()->format('Y-m-d'),
                            'attendance_type' => 8
                        ]);
                    }
                }
                $cont++;
            }

            DB::commit();
            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function updateWorkingDay(Request $request)
    {
        $this->validate($request,
        [
            'schedule_sessions_id' => 'required',
            'available' => 'required|gt:0',
        ],
        [
            'schedule_sessions_id' => 'Este campo es obligatorio',
            'available' => 'Este campo es obligatorio',
        ]);

        try {
            DB::beginTransaction();

            $id = $request->input('schedule_sessions_id');
            $schedule_sessions = ScheduleSessions::find($id);
            $shift_start = $request->input('shift_start_h').":".$request->input('shift_start_m')." ".$request->input('shift_start_type');
            $shift_end = $request->input('shift_end_h').":".$request->input('shift_end_m')." ".$request->input('shift_end_type');
            $schedule_sessions->available = $request->input('available');
            $schedule_sessions->shift_start = $shift_start;
            $schedule_sessions->shift_end = $shift_end;
            $schedule_sessions->working_time = $request->input('working_time');
            $schedule_sessions->break = $request->input('break');
            $schedule_sessions->save();

            DB::commit();
            return response()->json(['success' => true]);

        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }

    }

    public function getSchedules()
    {
        $result = "";
        $day = strtolower(date('D'));
        $setting_location_id = auth()->user()->setting_location_id;
        $locations = SettingLocation::join("setting_location_permissions", "setting_locations.id" , "=", "setting_location_permissions.location_id")->select("setting_locations.id", "setting_locations.name")
            ->where("setting_location_permissions.setting_location_id", "=" , $setting_location_id)
            ->get();

        $sessions_type = ScheduleSessionTypes::all();
        $array_days = ["mon", "tue", "wed", "thu", "fri", "sat", "sun"];
        $array_dia_mostrar = array("1"=>'Lunes',"2"=>'Martes',"3"=>'Miercoles',"4"=>'Jueves',"5"=>'Viernes',"6"=>'Sabado',"7"=>'Domingo');

        foreach ($locations as $key => $location)
        {
            if ($location->id == 1) {
                continue;
            }
            $location_id = $location->id;
            $location_name = $location->name;
            $result = $result."
            <div class='card'>
                <div class='card-header'>
                    <span class='span-title text-success'>".$location->name."</span>
                </div>
                <div class='card-body'>
                    <div class='nav-tabs-boxed'>
                        <ul class='nav nav-tabs' role='tablist'>";
                        foreach($sessions_type as $key => $session)
                        {
                            $active = ($key == 0)? "active" : "";
                            $result = $result."<li class='nav-item'><a class='nav-link $active' data-toggle='tab' href='#tab-".$session->id."-".$location->id."' role='tab' aria-controls='home' >$session->name</a></li>";
                        }
                        $result = $result."<li class='nav-item'><a class='nav-link' data-toggle='tab' href='#tab-5".$location->id."' role='tab' aria-controls='home' >Hoy</a></li>";
            $result = $result."</ul>";
            $result = $result."<div class='tab-content'>";
            $content = "";
            foreach($sessions_type as $key => $session)
                {
                    $session_id = $session->id;
                    $active = ($key == 0)? "active" : "";
                    $content = $content."<div class='tab-pane $active' id='tab-".$session->id."-".$location->id."' role='tabpanel'>";

                    //cuerpo de los tab
                    $exists_schedule = ScheduleSessions::where('setting_location_id', '=', $location_id)->where('session', '=', $session_id)->exists();
                    if ($exists_schedule == false)
                    {
                        $content = $content."<div class='alert alert-danger' role='alert'>No hay horarios creados para este turno</div>";
                        $content = $content."</div>";
                        continue;
                    }
                    else
                    {
                        $exists_schedule = Schedule::where('setting_location_id', '=', $location_id)->where('session', '=', $session_id)->exists();
                        if ($exists_schedule == false)
                        {
                            $content = $content."<div class='alert alert-warning' role='alert'>No hay modelos asignadas a este turno</div>";
                            $content = $content."</div>";
                            continue;
                        }
                        else
                        {
                            $content = $content."
                            <table class='table table-hover table-striped'>
                                <thead>
                                    <tr>
                                        <th>Modelo</th>
                                        <th>Lunes</th>
                                        <th>Martes</th>
                                        <th>Miércoles</th>
                                        <th>Jueves</th>
                                        <th>Viernes</th>
                                        <th>Sábado</th>
                                        <th>Domingo</th>
                                    </tr>
                                </thead>
                                <tbody>";

                            $schedules = Schedule::where('setting_location_id', '=', $location_id)->where('session', '=', $session_id)->get();
                            foreach ($schedules as $schedule)
                            {
                                $content = $content."<tr>";
                                $content = $content."<td>".$schedule->user->nick."</td>";

                                for ($j=0; $j < count($array_days); $j++)
                                {
                                    if ($schedule[$array_days[$j]] == 0)
                                       $tdbody = "";
                                    if ($schedule[$array_days[$j]] == 1)
                                       $tdbody = "<i class='fa fa-check text-success ml-3' data-toggle='tooltip' data-placement='top' title='En turno'></i>";
                                    if ($schedule[$array_days[$j]] == 2)
                                       $tdbody = "";
                                    if ($schedule[$array_days[$j]] == 3)
                                       $tdbody = "<i class='fas fa-bed text-info ml-3' data-toggle='tooltip' data-placement='top' title='Descanso'></i>";

                                    $content = $content."<td>".$tdbody."</td>";

                                }
                                $content = $content."</tr>";
                            }
                            $content = $content."<tr>";
                            $content = $content."<td>DISPONIBLE</td>";
                            $schedule_availability = ScheduleSessions::where('setting_location_id', '=', $location_id)->where('session', '=', $session_id)->get();
                            for ($k=0; $k < count($array_days); $k++) {
                                $day_available = $array_days[$k];
                                $days_reserved = Schedule::where('setting_location_id', '=', $location_id)->where('session', '=', $session_id)->where($day_available, '=', 1)->count();
                                $availables = $schedule_availability[0]->available - $days_reserved;
                                $content = $content."<td><span  class='text-danger ml-3 font-weight-bold'>".$availables."</span></td>";
                            }
                            $content = $content."</tr>";

                            $content = $content."</tbody></table>";

                            if(Auth::user()->can('schedule-edit')) {
                                $content = $content . "<a type='button' class='btn btn-m btn-warning float-right btn-sm' href='edit/$location_id/$session_id' ><i class='fas fa-pencil-alt'></i> Editar</a>";
                            }

                            $array_working_day = [
                                "location_id" => $location_id,
                                "session_id" => $session_id,
                                "schedule_availability" => $schedule_availability[0],
                                "session_name" => $schedule_availability[0]->type->name,
                            ];
                            $array_working_day = json_encode($array_working_day);

                            if(Auth::user()->can('schedule-edit')) {
                                $content = $content."<button type='button' class='btn btn-m btn-info float-right btn-sm mr-2' onclick='modalWorkingDay(".$array_working_day.")' data-toggle='modal' data-target='#modal-workingday-schedule' ><i class='fas fa-pencil-alt'></i> Jornada</button>";
                            }

                            $content = $content."</div>";
                        }

                    }

                }

            //tab hoy

            $content = $content."<div class='tab-pane' id='tab-5".$location->id."' role='tabpanel'>";
            $exist = Schedule::where('setting_location_id', '=', $location_id)->where($day, '=', 1)
                           ->orWhere('setting_location_id', '=', $location_id)->where($day, '=', 3)->exists();
            if ($exist == false)
            {
                $content = $content."<div class='alert alert-warning' role='alert'>No hay turnos para el dia de hoy</div>";
            }
            else
            {
                $content = $content."<div class='alert alert-success text-center font-weight-bold' role='alert'>".date('l')."</div>";
                $content = $content."<div class='row'>";
                foreach($sessions_type as $sessions)
                    {
                        $session_show = ($sessions->id == 3)? "M Tarde" : $sessions->name;
                        $content = $content."<div class='card col-lg-3'>
                            <div class='card-body'>
                                <table class='table table-striped table-hover'>
                                    <thead>
                                        <tr>
                                            <th>Modelo</th>
                                            <th>$session_show</th>
                                        </tr>
                                    </thead>
                                    <tbody>";
                        $results_day = Schedule::where('setting_location_id', '=', $location_id)->where($day, '=', 1)->where('session', '=', $sessions->id)
                           ->orWhere('setting_location_id', '=', $location_id)->where($day, '=', 3)->where('session', '=', $sessions->id)->get();

                        foreach ($results_day as $result_day)
                        {
                            if ($result_day[$day] == 0)
                                $tdbody = "";
                            if ($result_day[$day] == 1)
                                $tdbody = "<i class='fa fa-check text-success ml-3' data-toggle='tooltip' data-placement='top' title='En turno'></i>";
                            if ($result_day[$day] == 2)
                                $tdbody = "";
                            if ($result_day[$day] == 3)
                                $tdbody = "<i class='fas fa-bed text-info ml-3' data-toggle='tooltip' data-placement='top' title='Descanso'></i>";
                            $content = $content. "<tr>";
                            $content = $content. "<td>".$result_day->user->nick."</td>";
                            $content = $content. "<td>".$tdbody."</td>";
                            $content = $content. "</tr>";
                        }
                        $content = $content. "<tr>";

                        $schedule_availability = ScheduleSessions::where('setting_location_id', '=', $location_id)->where('session', '=', $sessions->id)->exists();
                        if ($schedule_availability) {
                            $schedule_availability = ScheduleSessions::where('setting_location_id', '=', $location_id)->where('session', '=', $sessions->id)->get();
                            $availables = $schedule_availability[0]->available;
                        }
                        else
                        {
                            $availables = 0;
                        }

                        $days_reserved = Schedule::where('setting_location_id', '=', $location_id)->where($day, '=', 1)->where('session', '=', $sessions->id)->count();

                        $availables = $availables - $days_reserved;
                        $content = $content. "<td>Disponible</td>";
                        $content = $content."<td><span  class='text-danger ml-3 font-weight-bold'>".$availables."</span></td>";
                        $content = $content. "</tr>";
                        $content = $content. "</tbody>
                                </table>
                            </div>
                        </div>";

                    }

                $content = $content."</div>";
            }

            $content = $content."</div>";

            $result = $result.$content;

            $result = $result."</div>";
            $result = $result."</div>";
            $result = $result."</div></div>";

        }
        return $result;
    }

    public function execute()
    {
        $min_id = 9999;
        $max_id = 11000;
        $horarios = DB::connection('gbmedia')->table('horario')->whereBetween('id', [$min_id, $max_id])->get();
        $msg = "Nothing bitch";

        foreach($horarios as $horario){
            $model = User::where('nick', $horario->usuario_modelo)->first();
            Schedule::updateOrCreate([
                'user_id' => $model->id,
                'mon' => ($horario->lunes == 'Si') ? 1 : 3,
                'tue' => ($horario->martes == 'Si') ? 1 : 3,
                'wed' => ($horario->miercoles == 'Si') ? 1 : 3,
                'thu' => ($horario->jueves == 'Si') ? 1 : 3,
                'fri' => ($horario->viernes == 'Si') ? 1 : 3,
                'sat' => ($horario->sabado == 'Si') ? 1 : 3,
                'sun' => ($horario->domingo == 'Si') ? 1 : 3,
                'setting_location_id' => $model->setting_location_id,
                'session' => $horario->sesion
            ]);

            $msg = "Done bitch";
        }

        return response()->json($msg);
    }

}
