<?php

namespace App\Http\Controllers\Booking;

use App\Events\AppointmentCreated;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Tasks\TaskController;
use App\Models\Bookings\Booking;
use App\Models\Bookings\BookingDay;
use App\Models\Bookings\BookingProcess;
use App\Models\Bookings\BookingSchedule;
use App\Models\Bookings\BookingType;
use App\Models\Bookings\Exonerate;
use App\Models\Bookings\QuarterDay;
use App\Models\Satellite\SatelliteOwner;
use App\Models\Satellite\SatellitePaymentAccount;
use App\Models\Satellite\SatellitePaymentDeduction;
use App\Models\Settings\SettingLocation;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskComment;
use App\Models\Tenancy\Tenant;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Traits\TraitGlobal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SebastianBergmann\Comparator\Book;

class BookingController extends Controller
{
    use TraitGlobal;

    function __construct()
    {
        $this->middleware('auth');

    }

    public function index($id)
    {
        $bookingid = BookingType::where('id', $id)->select('id')->first();
        $user_permission = Auth()->user()->getPermissionsViaRoles()->pluck('name');

        return view('adminModules.booking.index', compact('bookingid', 'user_permission'));
    }

    public function english($id)
    {
        $bookingid = BookingType::where('id', $id)->select('id')->first();
        $user_permission = Auth()->user()->getPermissionsViaRoles()->pluck('name');

        return view('adminModules.booking.english', compact('bookingid', 'user_permission'));
    }

    public function makeup($id)
    {
        $bookingid = BookingType::where('id', $id)->select('id')->first();
        $user_permission = Auth()->user()->getPermissionsViaRoles()->pluck('name');

        return view('adminModules.booking.makeup', compact('bookingid', 'user_permission'));
    }

    public function psychology($id)
    {
        $bookingid = BookingType::where('id', $id)->select('id')->first();
        $user_permission = Auth()->user()->getPermissionsViaRoles()->pluck('name');

        return view('adminModules.booking.psychology', compact('bookingid', 'user_permission'));
    }

    public function bookingModelView()
    {
        $location_id = auth()->user()->setting_location_id;
        $seed = SettingLocation::where('id', $location_id)->select('name')->first();
        
        return view('adminModules.booking.bookingModelView', compact('seed'));
    }

    public function store(Request $request)
    {
        $appointment = "";
        $date = $request->input('startDate');
        $date = date('Y-m-d', strtotime($date));
        $time = $request->input('time');
        $models = $request->input('models');
        $temp = explode('-', $date);
        $day = $temp[2];
        $month = $temp[1];
        $year = $temp[0];
        $status = 0;
        $fired = "";
        $error = "";

        $month_name = date("F", mktime(0, 0, 0, $month, $day));
        $date_range = $month_name . "-" . $year;
        $note = $request->input('description');
        $type = $request->input('booking_type_id');
        $loc_id = auth()->user()->setting_location_id;
        $has_appointment = false;
        $is_exonerated = false;
        
        $msg = "";
        $code = "";
        $icon = "";

        $is_blocked = Booking::where([
            ['date', $date],
            ['booking_schedule_id', $time],
            ['booking_type_id', $type]
        ])->exists();
        
        if ($is_blocked){
            $msg = "La fecha ya esta tomada o  bloqueada";
            $code = 403;
            $icon = "error";
        }else{

            foreach ($models as $key => $model) {
                $model_id = $model['id'];
                $model_nick = $model['nick'];

                if ($type !== 1)
                {
                    $has_appointment = Booking::where([['model_id', $model_id], ['date', $date], ['booking_type_id', $type]])->exists();
                    $is_exonerated = Exonerate::where([['user_id', $model_id], ['booking_type_id', $type]])->exists();
                }

                if ($is_exonerated || $has_appointment){
                    if ($is_exonerated){
                        if ($model_id == auth()->user()->id){
                            $msg = "Usted esta exonerado";
                        }else{
                            $msg = "El usuario esta exonerado";
                        }
                        $code = 403;
                        $icon = "warning";
                    }else{
                        if ($model_id == auth()->user()->id){
                            $msg = "Usted ya tiene citas para esta fecha";
                        }else{
                            $msg = "El usuario ya tiene citas para esta fecha";
                        }
                        $code = 403;
                        $icon = "warning";
                    }
                }else{
                    try {
                        DB::beginTransaction();
                        $appointment = Booking::create([
                            'booking_schedule_id' => $time,
                            'booking_type_id' => $type,
                            'user_id' => auth()->user()->id,
                            'model_id' => $model_id,
                            'nick' => $model_nick,
                            'status' => $status,
                            'date_range' => $date_range,
                            'date' => $date,
                            'day' => $day,
                            'month' => $month,
                            'year' => $year,
                            'description' => $note
                        ]);

                        if ($appointment){
                            try {
                                
                                event(new AppointmentCreated($appointment));
                                $fired = 'Event has been fired Successfully!';

                                $msg = "La reserva se realizo exitosamente";
                                $code = 200;
                                $icon = "success";

                            } catch(Exception $ex) {
                                $error = $ex;
                            }
                        }
                        DB::commit();
                    }catch (Exception $e){
                        DB::rollback();
                    }
                }
            }
        }

        return response()->json([
            'appointment' => $appointment,
            'msg' => $msg,
            'code' => $code,
            'icon' => $icon,
            'event' => $fired,
            'error' => $error
        ]);

    }

    public function agenda($id)
    {
        $agendas = Booking::join('booking_schedules', 'bookings.booking_schedule_id', '=', 'booking_schedules.id')
            ->select('bookings.*')
            ->addSelect('booking_schedules.id AS sch_id', 'booking_schedules.hour', 'booking_schedules.minutes', 'booking_schedules.meridiem', 'booking_schedules.booking_type_id')
            ->where('bookings.booking_type_id', $id)->get();
        $bookings = [];

        $min_date = Carbon::now()->startOfMonth();
        $max_date = Carbon::now()->addMonth()->endOfMonth();

        $sundays = $this->getSundaysInMonth($min_date, $max_date);

        foreach ($agendas as $agenda) {
            $nick = $agenda->nick;
            $start_date = $agenda->date;
            $hour = $agenda->hour;
            $minutes = $agenda->minutes;
            $meridiem = $agenda->meridiem;
            $status = $agenda->status;
            $comment = $agenda->description;
            $id = $agenda->id;
            $schedule = $hour.":".$minutes." ".$meridiem;
            $model_id = $agenda->model_id;
            $msg = "";
            
            $currentDate = Carbon::now()->format('Y-m-d');

            if ($agenda->status === '0'){
                $color = "#f39c12";
                $msg = "Reserva pendiente con ".$nick." a las ".$schedule;
                if ($start_date < $currentDate && $status === 0){
                    $msg = "La cita con ".$nick."nunca se actualizado";
                }
            }elseif ($agenda->status === '1'){
                $color = "#34AB24";
                $msg = "Asistió a reserva con ".$nick." a las ".$schedule;
            }elseif ($agenda->status === '2'){
                $color = "#EF191E";
                $msg = "La modelo ".$nick." no asistió a reserva a las ".$schedule;
            }else{
                $color = "#4c4f54";
                $msg = "Reservas ". $nick;
            }

            $bookings[] = [
                'title' => $schedule." ".$nick,
                'start' => $start_date,
                'end' => $start_date,
                'time' => $schedule,
                'time_id' => $agenda->sch_id,
                'status' => intval($status),
                'description' => $comment,
                'model_nick' => $nick,
                'model_id' => $model_id,
                'start_date' => $start_date,
                'agenda_id' => $id,
                'color' => $color,
                'tip' => $nick,
                'msg' => $msg,
                'sundays' => $sundays,
            ];
        }
        
        return response()->json([
            'bookings' => $bookings,
            'sundays' => $sundays
        ]);
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $status = $request->input('status');
        $sessionType = $request->input('sessionType');
        $date = $request->input('date');
        $model_id = $request->input('model');
        $actionType = "updated";
        $root = 594;
        $booking = Booking::findOrFail($id);
        $schedule_type_id = $booking->booking_schedule_id;
        $title = "";
        $process = "";
        $string_booking = "";
        $total = 0;

        $booking_schedule = BookingSchedule::findOrFail($schedule_type_id);
        $hour = $booking_schedule->hour;
        $minutes = $booking_schedule->minutes;
        $meridiem = $booking_schedule->meridiem;
        $time = $hour.":".$minutes." ".$meridiem;
        
        $appointment = $booking->update(['status' => $status]);

        if ($appointment){
            if ($status === 2){
               if ($booking->booking_type_id === 1 || $booking->booking_type_id === 3 || $booking->booking_type_id === 2){
                   $owner = SatelliteOwner::where('user_id', $model_id)->first();
                   $owner_id = $owner->id;
                   $owner_last_payment_date = SatellitePaymentAccount::where('owner_id', $owner_id)->max('payment_date');

                   if ($booking->booking_type_id === 1){
                       //audiovisuals
                       $total = 100000;
                       $string_booking = "Audiovisuales";
                   }elseif ($booking->booking_type_id === 3){
                       //makeup
                       $total = 50000;
                       $string_booking = "Maquillaje";
                   }elseif ($booking->booking_type_id === 2){
                       //English
                       $total = 50000;
                       $string_booking = "Ingles";
                   }

                   $deducction = new SatellitePaymentDeduction();
                   $deducction->payment_date = $owner_last_payment_date;
                   $deducction->owner_id = $owner_id;
                   $deducction->deduction_to = 2; // Valor pago
                   $deducction->total = $total;
                   $deducction->amount = $total;
                   $deducction->description = "Inasitencia a cita de ".$string_booking;
                   $deducction->type = 4;
                   $deducction->type_foreign_id = $booking->id;
                   $deducction->created_by = $root;
                   $deducction->save();
               }
            }
            
            try {
                event(new AppointmentCreated($appointment));
                $fired = 'Event has been fired Successfully!';
            } catch(Exception $ex) {
                $error = $ex;
            }
        }

        if ($status == 1 && ($sessionType == 1 || $sessionType == 2 || $sessionType == 3))
        {
            $process_status = 0;
            $temp = explode('-', $date);
            $day = $temp[2];
            $month = $temp[1];
            $year = $temp[0];
            $month_name = date("F", mktime(0, 0, 0, $month, $day));
            $date_range = $month_name . "-" . $year;
            $booking_type_id = 1;

            $photographer = $request->input('photographer');
            $photographer = (!empty($photographer) ? $photographer : NULL);
            if (!is_null($photographer)){
                $booking_type = "fotografía";
                $process = BookingProcess::create([
                    'booking_id' => $booking->id,
                    'user_id' => $photographer,
                    'model_id' => $model_id,
                    'process_status' => $process_status,
                    'booking_type' => $booking_type,
                    'booking_type_id' => $booking_type_id,
                    'date_range' => $date_range,
                    'session_date' => $date,
                ]);
            }

            $videographer = $request->input('videographer');
            $videographer = (!empty($videographer) ? $videographer : NULL);
            if (!is_null($videographer)){
                $booking_type = "video";
                $process = BookingProcess::create([
                    'booking_id' => $booking->id,
                    'user_id' => $videographer,
                    'model_id' => $model_id,
                    'process_status' => $process_status,
                    'booking_type' => $booking_type,
                    'booking_type_id' => $booking_type_id,
                    'date_range' => $date_range,
                    'session_date' => $date,
                ]);
            }

            return response()->json([
                'booking' => $booking,
                'process' => $process
            ]);

        }
        else{

            $model = User::where('id', $model_id)->first();
            $role_id = $model->setting_role_id;

            if ($model->nick != ""){
                $user = $model->nick;
            }else{
                $user = $model->first_name. " " .$model->last_name;
            }

           if ($status == 2){
               if($booking->booking_type_id == 1)
               {
                   $actionType = "updated";
                   $created_by = 10; // Audiovisuales
                   $created_by_type = 0;
                   if ($actionType == "updated"){
                       $title = "Audiovisuales: " .$user;
                   }
                   $receivers = [
                       'to_roles' => [
                           ['id' => 9, 'name' => 'Fotografo/a'],
                           ['id' => 10, 'name' => 'Videografo/a'],
                           ['id' => 1, 'name' => 'Gerente']
                       ],
                       'to_users' => [
                           ['id' => $model_id, 'name' => $user]
                       ],
                       'to_models' => [],
                   ];

               }
               elseif($booking->booking_type_id == 2){

                   $created_by = 18; // English
                   $created_by_type = 2;
                   if ($actionType == "updated") {
                       $title = "Ingles: " . $user;
                   }
                   $receivers = [
                       'to_roles' => [
                           ['id' => 18, 'name' => '	Profesor/a Inglés'],
                           ['id' => 1, 'name' => 'Gerente']
                       ],
                       'to_users' => [
                           ['id' => $model_id, 'name' => $user]
                       ],
                       'to_models' => [],
                   ];

               }
               elseif($booking->booking_type_id == 3){

                   $created_by = 17; // Maquillaje
                   $created_by_type = 0;
                   if ($actionType == "updated") {
                       $title = "Maquillaje: " . $user;
                   }

                   $receivers = [
                       'to_roles' => [
                           ['id' => 17, 'name' => 'Maquillador/a'],
                           ['id' => 1, 'name' => 'Gerente']
                       ],
                       'to_users' => [
                           ['id' => $model_id,  'name' => $user]
                       ],
                       'to_models' => [],
                   ];

               }
               else{

                   $created_by = 36; // Psicologia
                   $created_by_type = 0;
                   if ($actionType == "updated") {
                       $title = "Psicologia: " . $user;
                   }
                   $receivers = [
                       'to_roles' => [
                           ['id' => 36, 'name' => 'Psicólogo/a'],
                           ['id' => 1, 'name' => 'Gerente']
                       ],
                       'to_users' => [
                           ['id' => $model_id, 'name' => $user]
                       ],
                       'to_models' => [],
                   ];
               }

               $this->createBookingTask($user, $receivers, $title, $created_by, $role_id, $date, $actionType, $time, $newDate = null, $newTime = null);
           }

            return response()->json([
                'booking' => $booking,
                'actionType' => $actionType
            ]);
        }
    }

    public function destroy($id)
    {
        $delete = Booking::where('id', $id)->delete();

        return response()->json([
            'data' => $delete
        ]);
    }

    public function createSchedule(Request $request)
    {
        $hour = $request->input('hh');
        $minutes = $request->input('mm');
        $meridiem = $request->input('A');
        $bookingType = $request->input('booking_type_id');
        $booking = "";

        $exist = BookingSchedule::where([
            ['hour', $hour],
            ['minutes', $minutes],
            ['meridiem', $meridiem],
            ['booking_type_id', $bookingType]
        ])->exists();

        if ($exist){
            $msg = "Ya hay un horario con esta especificacion";
            $icon = "error";
            $code = 403;
        }else{
            $msg = "Horario creado exitosamente!";
            $icon = "success";
            $code = 200;
            
            $booking = BookingSchedule::create([
                'hour' => $hour,
                'minutes' => $minutes,
                'meridiem' => $meridiem,
                'booking_type_id' => $bookingType
            ]);
        }

        return response()->json([
            'booking' => $booking,
            'msg' => $msg,
            'icon' => $icon,
            'code' => $code
        ]);
    }

    public function getSchedule()
    {
        $schedules = BookingSchedule::select('id', 'hour', 'minutes', 'meridiem')->get();

        return response()->json([
            'schedules' => $schedules
        ]);
    }

    public function getScheduleById($id)
    {
        $schedules = BookingSchedule::where('booking_type_id', $id)->get();
        $min_date = Carbon::now()->firstOfMonth()->format('Y-m-d');
        $max_date = Carbon::now()->addMonth()->endOfMonth()->format('Y-m-d');
        $loc_id = auth()->user()->setting_location_id;

        $data = [];

        foreach ($schedules as $key => $schedule){
            $seedDays = BookingSchedule::leftjoin('booking_quarters', 'booking_quarters.booking_type_id', 'booking_schedules.booking_type_id')
                ->leftjoin('booking_days', 'booking_quarters.booking_day_id', 'booking_days.id')
                ->where([['booking_quarters.booking_type_id', $id], ['booking_quarters.setting_location_id', $loc_id]])
                ->select('booking_days.*')->groupBy('booking_days.id')->get();

            $data['schedules'][] = [
                'location_id' =>  $loc_id,
                'sch_id' =>  $schedule->id,
                'hour' =>  $schedule->hour.":".$schedule->minutes." ".$schedule->meridiem
            ];

            foreach($seedDays as $i => $seed){
                if ($seed->day_name == "Lunes"){
                    $day = "Monday";
                }elseif($seed->day_name == "Martes"){
                    $day = "Tuesday";
                }elseif($seed->day_name == "Miercoles"){
                    $day = "Wednesday";
                }elseif ($seed->day_name == "Jueves"){
                    $day = "Thursday";
                }elseif ($seed->day_name == "Viernes"){
                    $day = "Friday";
                }elseif ($seed->day_name == "Sabado"){
                    $day = "Saturday";
                }

                $data['schedules'][$key]['days'][$id][] = $this->getDaysInRange($min_date, $max_date, $day);
            }
        }

        return response()->json([
            'schedules' => $schedules,
            'data' => $data
        ]);
    }

    public function getBookingTypes()
    {
        $bookingTypes = BookingType::select('id', 'booking', 'type')->get();

        $data = [];
        $loc_id = auth()->user()->setting_location_id;

        $min_date = Carbon::now()->firstOfMonth()->format('Y-m-d');
        $max_date = Carbon::now()->addMonth()->endOfMonth()->format('Y-m-d');

        foreach($bookingTypes as $key => $schedule){
            $seedDays = BookingType::leftjoin('booking_quarters', 'booking_quarters.booking_type_id', 'booking_types.id')
                ->leftjoin('booking_days', 'booking_quarters.booking_day_id', 'booking_days.id')
                ->where([['booking_quarters.setting_location_id', "=", $loc_id], ['booking_quarters.booking_type_id', "=", $schedule->id]])->get();

            $location_name = SettingLocation::where('id', $loc_id)->select('name')->first();

            $data['booking_types'][] = [
                'id' =>  $schedule->id,
                'booking' => $schedule->booking,
                'location_id' => $loc_id,
                'location_name' => $location_name->name,
            ];

            foreach($seedDays as $i => $seed){
                if ($seed->day_name == "Lunes"){
                    $day = "monday";
                }elseif($seed->day_name == "Martes"){
                    $day = "tuesday";
                }elseif($seed->day_name == "Miercoles"){
                    $day = "wednesday";
                }elseif ($seed->day_name == "Jueves"){
                    $day = "thursday";
                }elseif ($seed->day_name == "Viernes"){
                    $day = "friday";
                }else{
                    $day = "saturday";
                }

                $data['booking_types'][$key]['days'][$loc_id][] = $this->getDaysInRange($min_date, $max_date, $day);
            }
        }
        
        return response()->json([
            'data' => $data
        ]);
    }

    public function getModelSchedule($id)
    {
        $agendas = Booking::join('booking_schedules', 'bookings.booking_schedule_id', '=', 'booking_schedules.id')
            ->where('model_id', $id)
            ->get();
        $bookings = [];
        $disabled = "";

        $min_date = Carbon::now()->startOfMonth();
        $max_date = Carbon::now()->addMonth()->endOfMonth();
        $sundays = $this->getSundaysInMonth($min_date, $max_date);

        foreach ($agendas as $agenda) {

            $start_date = $agenda->date;
            $hour = $agenda->hour;
            $minutes = $agenda->minutes;
            $meridiem = $agenda->meridiem;
            $time = $hour.":".$minutes." ".$meridiem;
            $status = $agenda->status;

            if ($agenda->booking_type_id === 1){
                if ($agenda->status === '4'){
                    $title = "Audiovisuales bloqueado";
                    $disabled = "disabled";
                }else{
                    $title = "Audiovisuales - ".$time;
                    $icon = "fas fa-video";
                }
            }elseif ($agenda->booking_type_id === 2){
                if ($agenda->status === '4'){
                    $title = "Ingles bloqueado";
                    $disabled = "disabled";
                }else{
                    $title = "Inglés - ".$time;
                    $icon = "fas fa-globe";
                }
            }elseif ($agenda->booking_type_id === 3){
                if ($agenda->status === '4'){
                    $title = "Maquillaje bloqueado";
                    $disabled = "disabled";
                }else{
                    $title = "Maquillaje - ".$time;
                    $icon = "fas fa-paint-brush";
                }
            }else{
                if ($agenda->status === '4'){
                    $title = "Psicología bloqueado";
                    $disabled = "disabled";
                }else{
                    $title = "Psicología - ".$time;
                    $icon = "fas fa-notes-medical";
                }
            }

            $simple_title = explode('-', $title);
            $simple_title = $simple_title[0];

            $user = User::join('booking_processes', 'booking_processes.user_id', 'users.id')
                ->where('booking_processes.id', $agenda->id)
                ->select('users.first_name', 'users.last_name')->first();
            
            if (!empty($user)){
                $user_name = $user->first_name." ".$user->last_name;
            }else{
                $user_name = "";
            }

            if ($agenda->status === '0'){
                $color = "#f39c12";
                $msg = "Reserva pendiente para ".$simple_title." a las ".$time;
            }elseif ($agenda->status === '1'){
                $color = "#34AB24";
                $msg = "Asistió a reserva para ".$simple_title." a las ".$time. " con ".$user_name;
            }elseif ($agenda->status === '2'){
                $color = "#EF191E";
                $msg = "No asistió a reserva para ".$simple_title." a las ".$time;
            }else{
                $color = "#4c4f54";
                $msg = "Reservas están deshabilitadas";
            }

            $bookings[] = [
                'title' => $title,
                'start' => $start_date,
                'end' => $start_date,
                'color' => $color,
                'status' => $status,
                'time' => $time,
                'tip' => $title,
                'msg' => $msg,
                'disabled' => $disabled
            ];
        }

        return response()->json([
            'bookings' => $bookings,
            'sundays' => $sundays
        ]);
    }

    public function getProcessesById($id)
    {
        $processes = BookingProcess::join('users', 'booking_processes.user_id', '=', 'users.id')
            ->select('booking_processes.*', 'users.first_name', 'users.last_name')
            ->where('booking_id', $id)->get();

        $data = [];

        foreach ($processes as $process){
            $booking_type = $process->booking_type;
            $last_name = $process->last_name;
            $first_name = $process->first_name;
            $process_status = $process->process_status;
            $booking_type_id = $process->booking_type_id;
            $process_id = $process->id;
            $attachment = $process->attachment;

            if ($process_status == 0){
                $msg = "<span class='text-warning font-weight-bold'>Sin revisar<span class='text-success'>";
                $status = 0;
            }elseif ($process_status == 1){
                $msg = "<span class='text-success font-weight-bold'>Entregado para revision</span>";
                $status = 1;
            }elseif ($process_status == 2){
                $msg = "<span class='text-success font-weight-bold'>Finalizado</span>";
                $status = 2;
            }else{
                $msg = "<span class='text-danger font-weight-bold'>Rechazado</span>";
                $status = 4;
            }

            $data[] = [
                'booking_type' => $booking_type,
                'name' => $first_name." ".$last_name,
                'process_status_msg' => $msg,
                'process_status' => $status,
                'process_id' => $process_id,
                'booking_type_id' => $booking_type_id,
                'attachment' => $attachment
            ];

        }

        return response()->json([
            'processes' => $data
        ]);
    }

    public function getProcessesByDate()
    {
        $min_date = BookingProcess::whereIn('process_status', [0, 1])->min('submitted_date');
        $max_date = BookingProcess::whereIn('process_status', [0, 1])->max('submitted_date');

        $weeks = $this->getDistinctWeeksBetweenDates($min_date, $max_date);
        $data = [];

        foreach ($weeks as $key => $week){
            $records = BookingProcess::join('users', 'booking_processes.user_id', '=', 'users.id')
                ->select('booking_processes.*')->addSelect('users.id as id_user','users.first_name', 'users.last_name', 'users.status')
                ->whereIn('booking_processes.process_status', [0, 1])
                ->whereBetween('booking_processes.session_date', [$week->start, $week->end])->get();

            $has_process  = false;
            foreach ($records as $process){
                $model = User::where('id', $process->model_id)->where('status', 1)->select('nick')->first();
               if (is_null($model)){
                   continue;
               }
                $booking_type = $process->booking_type;
                $last_name = $process->last_name;
                $first_name = $process->first_name;
                $process_status = $process->process_status;
                $booking_type_id = $process->booking_type_id;
                $session_date = $process->session_date;
                $process_id = $process->id;
                $attachment = $process->attachment;
                $user_id = $process->user_id;

                if ($process_status == 0){
                    $msg = "<span class='text-warning font-weight-bold'>Sin revisar<span>";
                    $status = 0;
                    $color = '';
                }elseif ($process_status == 1){
                    $msg = "<span class='text-success font-weight-bold'>En revision</span>";
                    $status = 1;
                    $color = '';
                }elseif ($process_status == 2){
                    $msg = "<span class='text-success font-weight-bold'>Finalizado</span>";
                    $status = 2;
                    $color = '';
                }else{
                    $msg = "<span class='text-danger font-weight-bold'>Rechazado</span>";
                    $status = 4;
                    $color = 'table-danger';
                }
                
                $cannotSee = false;
                if($process->user_id === auth()->user()->id){
                  $cannotSee = true;
                  $has_process = true;
                }
                
                if(auth()->user()->setting_role_id === 11 || auth()->user()->setting_role_id === 1){
                    $cannotSee = true;
                    $has_process = true;
                }

                $data[$key]['bookings'][] = [
                    'booking_type' => $booking_type,
                    'name' => $first_name." ".$last_name,
                    'process_status_msg' => $msg,
                    'process_status' => $status,
                    'process_id' => $process_id,
                    'booking_type_id' => $booking_type_id,
                    'attachment' => $attachment,
                    'session_date' => $session_date,
                    'model' => $model->nick,
                    'user_id' => $user_id,
                    'table_color' => $color,
                    'cansee' => $cannotSee,
                ];

                $data[$key]['date_range'] = $week->formatted;
                $data[$key]['auth_user'] = auth()->user()->id;
                $data[$key]['role_id'] = auth()->user()->setting_role_id;
                $data[$key]['has_process'] = $has_process;
            }
        }

        return response()->json([
            'data' => $data,
            'min' => $min_date,
            'max' => $max_date,
            'w' => $weeks
        ]);

    }

    public function updateProcess($id, Request $request)
    {
        $process = BookingProcess::findOrFail($id);
        $status = $request->input("process_status");
        $submitted_date = Carbon::now();

        if ($status == 1){
            if ($request->has("attachment")){
                $attachment = $request->input("attachment");
                $process->update([
                    'process_status' => $status,
                    'submitted_date' => $submitted_date,
                    'attachment' => $attachment
                ]);
            }else{
                $process->update([
                    'process_status' => $status,
                    'submitted_date' => $submitted_date
                ]); 
            }
        }elseif ($status == 2){
            $review_date = Carbon::now();
            $process->update([
                'process_status' => $status,
                'review_date' => $review_date
            ]);
        }else{
            $review_date = Carbon::now();
            $process->update([
                'process_status' => $status,
                'review_date' => $review_date
            ]);
        }

        return response()->json([
            'process' => $process
        ]);
    }

    public function deleteProcess($id)
    {
        $toBeDeleted = BookingProcess::findOrFail($id);
        $toBeDeleted->delete();

        return response()->json(['data' => $toBeDeleted]);
    }

    public function getBookingsByDate($id, Request $request)
    {
        $locations = SettingLocation::where('name', '!=', 'all')->select('id', 'name')->orderBy('id', 'ASC')->get();
        $ranges = Booking::select('date_range')->where('booking_type_id', $id)->groupBy('date_range')->get();
        $data = [];
        $model_role_id = 14;

        if ($request->has('date_range')){
            $date_range = $request->input('date_range');
        }else{
            $date = Carbon::now()->format('Y-m-d');
            $temp = explode('-', $date);
            $year = $temp[0];
            $month = $temp[1];
            $month_name = date("F", mktime(0, 0, 0, $month));
            $date_range = $month_name . "-" . $year;
        }

        foreach ($locations as $key => $location){

            $users = User::where([
                ['users.setting_role_id', $model_role_id],
                ['users.setting_location_id', $location->id],
                ['users.status', '=', 1],
            ])->get();

            $booking = BookingType::where('id', $id)->select('booking')->first();
            $data[] = ['booking' => $booking->booking];
            $data['locations'][] = [
                'id' =>  $location->id,
                'location_name' => $location->name,
                'date_range' => $date_range,
                'auth_user' => auth()->user()->id,
                'auth_role_id' => auth()->user()->setting_role_id
            ];

            foreach ($users as $user){

                $total_bookings = Booking::select('status')->where([
                    ['model_id', $user->id],
                    ['booking_type_id', $id],
                    ['date_range', $date_range]
                ])->count();

                $pending_bookings = Booking::select('status')->where([
                    ['model_id', $user->id],
                    ['booking_type_id', $id],
                    ['date_range', $date_range],
                    ['status', 0]
                ])->count();

                $attended_bookings = Booking::select('status')->where([
                    ['model_id', $user->id],
                    ['booking_type_id', $id],
                    ['date_range', $date_range],
                    ['status', 1]
                ])->count();

                $nonattended_bookings = Booking::select('status')->where([
                    ['model_id', $user->id],
                    ['booking_type_id', $id],
                    ['date_range', $date_range],
                    ['status', 2]
                ])->count();

                $data['locations'][$key]['users'][] = [
                    'nick' => $user->nick,
                    'location_id' => $user->setting_location_id,
                    'role' => $user->setting_role_id,
                    'user_id' => $user->id,
                    'avatar' => global_asset("../storage/app/public/" . tenant('studio_slug') . "/avatars/" . $user->avatar),
                    'total_bookings' => "<span class='font-weight-bold text-info'>". $total_bookings ."</span>",
                    'pending_bookings' => "<span class='font-weight-bold text-warning'>". $pending_bookings ."</span>",
                    'attended_bookings' => "<span class='font-weight-bold text-success'>". $attended_bookings ."</span>",
                    'nonattended_bookings' => "<span class='font-weight-bold text-danger'>". $nonattended_bookings ."</span>"
                ];
            }
        }
        
        return response()->json([
            'data' => $data,
            'ranges' => $ranges
        ]);
        
    }

    public function getBookingFinished()
    {
        $min_date = BookingProcess::whereIn('process_status', [2])->min('submitted_date');
        $max_date = BookingProcess::whereIn('process_status', [2])->max('submitted_date');

        $weeks = $this->getDistinctWeeksBetweenDates($min_date, $max_date);
        $data = [];

        foreach ($weeks as $key => $week){
            $records = BookingProcess::join('users', 'booking_processes.user_id', '=', 'users.id')
                ->select('booking_processes.*')->addSelect('users.first_name', 'users.last_name', 'users.avatar')
                ->whereIn('booking_processes.process_status', [2])
                ->whereBetween('booking_processes.submitted_date', [$week->start, $week->end])->get();

            $has_process  = false;
            foreach ($records as $process){
                $model = User::where('id', $process->model_id)->select('nick')->first();
                $booking_type = $process->booking_type;
                $last_name = $process->last_name;
                $first_name = $process->first_name;
                $booking_type_id = $process->booking_type_id;
                $session_date = $process->session_date;
                $process_id = $process->id;
                $attachment = $process->attachment;
                $user_id = $process->user_id;
                $review_date = $process->review_date;
                $submitted_date = $process->submitted_date;

                $cannotSee = false;
                if($process->user_id === auth()->user()->id){
                    $cannotSee = true;
                    $has_process = true;
                }

                if(auth()->user()->setting_role_id === 11 || auth()->user()->setting_role_id === 1){
                    $cannotSee = true;
                    $has_process  = true;
                }

                $data[$key]['bookings'][] = [
                    'booking_type' => $booking_type,
                    'name' => $first_name." ".$last_name,
                    'process_id' => $process_id,
                    'booking_type_id' => $booking_type_id,
                    'attachment' => $attachment,
                    'session_date' => $session_date,
                    'submitted_date' => $submitted_date,
                    'review_date' => $review_date,
                    'model' => $model->nick,
                    'user_id' => $user_id,
                    'avatar' => $process->avatar,
                    'cansee' => $cannotSee
                ];

                $data[$key]['date_range'] = $week->formatted;
                $data[$key]['auth_user'] = auth()->user()->id;
                $data[$key]['role_id'] = auth()->user()->setting_role_id;
                $data[$key]['has_process'] = $has_process;
            }
        }

        return response()->json([
            'data' => $data,
            'min' => $min_date,
            'max' => $max_date
        ]);

    }

    public function blockDate(Request $request)
    {
         $schedules = $request->input('time');
         $booking_type_id = $request->input('booking_type_id');
         $date = $request->input('startDate');
         $status = 4;
         $date = date('Y-m-d', strtotime($date));
         $temp = explode('-', $date);
         $day = $temp[2];
         $month = $temp[1];
         $year = $temp[0];
         $month_name = date("F", mktime(0, 0, 0, $month, $day));
         $date_range = $month_name . "-" . $year;
         $booking = "";
         $code = "";
         $icon = "";
         $msg = "";
         
        foreach ($schedules as $schedule){
            $is_blocked = Booking::where([
                ['date', $date],
                ['booking_schedule_id', $schedule],
                ['booking_type_id', $booking_type_id]
            ])->exists();
            
            if ($is_blocked){
                $msg = "La fecha ya esta tomada o  bloqueada";
                $code = 403;
                $icon = "error";
            }else{
                $booking = Booking::create([
                    'booking_schedule_id' => $schedule['id'],
                    'booking_type_id' => $booking_type_id,
                    'user_id' => auth()->user()->id,
                    'model_id' => auth()->user()->id,
                    'nick' => "deshabilitado por " . auth()->user()->first_name . " " . auth()->user()->last_name,
                    'status' => $status,
                    'date_range' => $date_range,
                    'date' => $date,
                    'day' => $day,
                    'month' => $month,
                    'year' => $year,
                ]);
                
                $msg = "La reserva se bloqueó exitosamente";
                $code = 200;
                $icon = "success";
            }
        }
        
        return response()->json([
            'booking' => $booking,
            'msg' => $msg,
            'code' => $code,
            'icon' => $icon
        ]);
    }

    public function exonerate(Request $request)
    {
        $type = $request->input("type");
        $users = $request->input("user_id");
        $user_exonerated = [];

        $msg = "";
        $icon = "";
        $code = "";

        foreach ($users as $user){
            $exonerated = Exonerate::where('user_id', $user['id'])->exists();

            if ($exonerated){
                $msg = "Usuario ya ha sido exonerado!";
                $icon = "error";
                $code = 403;
            }else{
                $msg = "Usuario exonerado!";
                $icon = "success";
                $code = 200;
                $user_exonerated [] = Exonerate::create([
                    'user_id' => $user['id'],
                    'booking_type_id' => $type
                ]);
            }
        }

        return response()->json([
            'user' => $user_exonerated,
            'msg' => $msg,
            'icon' => $icon,
            'code' => $code
        ]);
    }

    public function getDays()
    {
        $days = BookingDay::select('id', 'day_name')->get();

        return response()->json([
            'days' => $days
        ]);
    }

    public function getLocations()
    {
         $locations = SettingLocation::where('name', '!=', 'all')->select('id', 'name')->get();

         return response()->json([
             'locations' => $locations
         ]);
    }

    public function getExonerated($id)
    {
        $data = Exonerate::join('users', 'booking_exonerates.user_id', 'users.id')->select('booking_exonerates.id', 'users.nick')->where('booking_type_id', $id)->get();

        return response()->json([
            'data' => $data
        ]);
    }

    public function deleteExonerate($id)
    {
        $delete = Exonerate::findOrFail($id);
        $delete->delete();

        return response()->json([
            'data' => $delete
        ]);
    }

    public function reschedule($id, Request $request)
    {
        $booking = Booking::where('id', $id)->first();
        $schedule_type_id = $booking->booking_schedule_id;
        $originalDate = $booking->date;
        $model_id = $booking->model_id;

        $booking_schedule = BookingSchedule::findOrFail($schedule_type_id);
        $hour = $booking_schedule->hour;
        $minutes = $booking_schedule->minutes;
        $meridiem = $booking_schedule->meridiem;
        $originalTime = $hour.":".$minutes." ".$meridiem;

        $newDate = $request->input('startDate');
        $newTime_id = $request->input('time');
        $actionType = "reschedule";

        $booking_schedule = BookingSchedule::findOrFail($newTime_id);
        $newHour = $booking_schedule->hour;
        $newMinutes = $booking_schedule->minutes;
        $newMeridiem = $booking_schedule->meridiem;
        $newTime = $newHour.":".$newMinutes." ".$newMeridiem;

        $currentDate = Carbon::now()->format('Y-m-d');

        $was_rescheduled = 1;
        $rescheduled_by = auth()->user()->id;
        $appointment = "";
        $fired = "";
        $error = "";

        $whatDay = Carbon::parse($newDate)->format('l');
        if ($whatDay === "Sunday")
        {
            $msg = "No puede reprogramar citas para los Domingos";
            $icon = "error";
            $code = 403;

            return response()->json([
                'msg' => $msg,
                'icon' => $icon,
                'code' => $code
            ]);
        }
        
        if ($newDate < $currentDate){
            $msg = "No puede reprogramar para esta fecha";
            $icon = "error";
            $code = 403;
        }else{
            $appointment = $booking->update([
                'date' => $newDate, 
                'booking_schedule_id' => $newTime_id,
                'was_rescheduled' => $was_rescheduled,
                'rescheduled_by' => $rescheduled_by
            ]);
            $msg = "Cita reprogramada exitosamente";
            $icon = "success";
            $code = 200;

            if ($appointment){
                try {
                    event(new AppointmentCreated($appointment));
                    $fired = 'Event has been fired Successfully!';
                } catch(Exception $ex) {
                    $error = $ex;
                }
            }
        }

        $model = User::where('id', $booking->model_id)->first();
        $role_id = $model->setting_role_id;

        if ($model->nick != ""){
            $user = $model->nick;
        }else{
            $user = $model->first_name. " " .$model->last_name;
        }

        if($booking->booking_type_id == 1){

            $created_by = 10;
            $title = "Audiovisuales cambio de reserva: " .$user;
            $receivers = [
                'to_roles' => [
                    ['id' => 9, 'name' => 'Fotografo/a'],
                    ['id' => 10, 'name' => 'Videografo/a'],
                    ['id' => 1, 'name' => 'Gerente']
                ],
                'to_users' => [
                    ['id' => $model_id, 'name' => $user]
                ],
                'to_models' => [],
            ];

        }elseif($booking->booking_type_id == 2){

            $created_by = 18; // English
            $title = "Inglés cambio de reserva: " .$user;
            $receivers = [
                'to_roles' => [
                    ['id' => 18, 'name' => 'Profesor/a Inglés'],
                    ['id' => 1, 'name' => 'Gerente']
                ],
                'to_users' => [
                    ['id' => $model_id, 'name' => $user]
                ],
                'to_models' => [],
            ];

        }elseif($booking->booking_type_id == 3){

            $created_by = 17; // Maquillaje
            $title = "Maquillaje cambio de reserva: " .$user;
            $receivers = [
                'to_roles' => [
                    ['id' => 17, 'name' => 'Maquillador/a'],
                    ['id' => 1, 'name' => 'Gerente']
                ],
                'to_users' => [
                    ['id' => $model_id, 'name' => $user]
                ],
                'to_models' => [],
            ];

        }else{

            $created_by = 36; // Psicologia
            $title = "Psicología cambio de reserva: " .$user;
            $receivers = [
                'to_roles' => [
                    ['id' => 36, 'name' => 'Psicólogo/a'],
                    ['id' => 1, 'name' => 'Gerente']
                ],
                'to_users' => [
                    ['id' => $model_id, 'name' => $user]
                ],
                'to_models' => [],
            ];
        }

        $this->createBookingTask($user, $receivers, $title, $created_by, $role_id, $originalDate, $actionType, $originalTime, $newDate, $newTime);
        
        return response()->json([
            'booking' => $booking,
            'msg' => $msg,
            'icon' => $icon,
            'code' => $code,
            'appointment' => $appointment,
            'fired' => $fired,
            'error' => $error
        ]);
    }

    public function addMoreModels(Request $request)
    {
        $date = $request->input('startDate');
        $date = date('Y-m-d', strtotime($date));
        $time = $request->input('time');
        $models = $request->input('models');
        $temp = explode('-', $date);
        $day = $temp[2];
        $month = $temp[1];
        $year = $temp[0];
        $status = 0;

        $month_name = date("F", mktime(0, 0, 0, $month, $day));
        $date_range = $month_name . "-" . $year;
        $note = $request->input('description');
        $type = $request->input('booking_type_id');

        $booking = "";
        $msg = "";
        $icon = "";
        $code = "";

        $temp_time = explode(':', $time);
        $hour = $temp_time[0];
        $time_id = BookingSchedule::where([['hour', $hour], ['booking_type_id', $type]])->select('id')->first();

        foreach ($models as $key => $model) {
            $model_id = $model['id'];
            $model_nick = $model['nick'];

            $has_appointment = Booking::where([['model_id', $model_id], ['date', $date], ['booking_type_id', $type]])->exists();

            if ($has_appointment){
                $msg = "Este usuario ya tiene una cita pendiente";
                $icon = "error";
                $code = 403;
            }else{
                $booking = Booking::create([
                    'booking_schedule_id' => $time_id->id,
                    'booking_type_id' => $type,
                    'user_id' => auth()->user()->id,
                    'model_id' => $model_id,
                    'nick' => $model_nick,
                    'status' => $status,
                    'date_range' => $date_range,
                    'date' => $date,
                    'day' => $day,
                    'month' => $month,
                    'year' => $year,
                    'description' => $note
                ]);

                $msg = "Cita agregada exitosamente";
                $icon = "success";
                $code = 200;
            }
        }

        return response()->json([
            'booking' => $booking,
            'msg' => $msg,
            'icon' => $icon,
            'code' => $code
        ]);
    }

    public function allocateDays(Request $request)
    {
        $days = $request->input('days');
        $booking_type = $request->input('booking_type_id');

        $booking = [];
        $msg = "";
        $icon = "";
        $code = "";

        foreach ($days as $key => $day) {

            $day_id = $day['day']['id'];
            $location_id = $day['location']['id'];

            $msg = "Reservas para locaciones guardadas correctamente!";
            $icon = "success";
            $code = 200;

            $booking[] = QuarterDay::updateOrCreate([
                'booking_day_id' => $day_id,
                'setting_location_id' => $location_id,
                'booking_type_id' => $booking_type
            ]);
        }

        return response()->json([
            'booking' => $booking,
            'msg' => $msg,
            'icon' => $icon,
            'code' => $code
        ]);
    }

    public function seedDays($id)
    {
        $days = QuarterDay::join('setting_locations', 'booking_quarters.setting_location_id', 'setting_locations.id')
            ->where('booking_type_id', $id)
            ->select('booking_quarters.id', 'booking_quarters.setting_location_id', 'booking_quarters.booking_day_id', 'booking_quarters.booking_type_id')
            ->addSelect('setting_locations.name')->orderBy('id', 'ASC')
            ->get();

        return response()->json([
            'days' => $days
        ]);
    }

    public function updateAllocateDays(Request $request)
    {
        $days = $request->input('days');

        $booking = [];
        $msg = "";
        $icon = "";
        $code = "";

        foreach ($days as $key => $day) {

            $day_id = $day['booking_day_id'];
            $location_id = $day['setting_location_id'];
            $booking_id = $day['booking_type_id'];

            $booking = QuarterDay::where([['booking_day_id', '=', $day_id], ['booking_type_id', '=', $booking_id]])->first();

            $booking->update([
                'booking_day_id' => $day_id,
                'setting_location_id' => $location_id,
                'booking_type_id' => $booking_id
            ]);

            $msg = "Reservas para locaciones guardadas correctamente!";
            $icon = "success";
            $code = 200;;

            /*$booking[] = QuarterDay::updateOrCreate([
                'booking_day_id' => $day_id,
                'setting_location_id' => $location_id,
                'booking_type_id' => $booking_id
            ]);*/
        }

        return response()->json([
            'booking' => $booking,
            'msg' => $msg,
            'icon' => $icon,
            'code' => $code
        ]);
    }

    public function daysBySeed($id){

        $min_date = Carbon::now()->firstOfMonth()->format('Y-m-d');
        $max_date = Carbon::now()->addMonth()->endOfMonth()->format('Y-m-d');
        $loc_id = auth()->user()->setting_location_id;

        dd($min_date);
        $locations = SettingLocation::where('name', '!=', 'all')->select('id', 'name')->orderBy('id', 'ASC')->get();
        $data = [];

        foreach ($locations as $key => $location){
            $seedDays = QuarterDay::join('booking_days', 'booking_quarters.booking_day_id', 'booking_days.id')
                ->where([['booking_quarters.booking_type_id', $id], ['booking_quarters.setting_location_id', $location->id]])
                ->get();

            $data['locations'][] = [
                'id' =>  $location->id,
                'location_name' => $location->name
            ];


            foreach($seedDays as $i => $seed){
                if ($seed->day_name == "Lunes"){
                    $day = "monday";
                }elseif($seed->day_name == "Martes"){
                    $day = "tuesday";
                }elseif($seed->day_name == "Miercoles"){
                    $day = "wednesday";
                }elseif ($seed->day_name == "Jueves"){
                    $day = "thursday";
                }elseif ($seed->day_name == "Viernes"){
                    $day = "friday";
                }else{
                    $day = "saturday";
                }
            }
            
            $data['locations'][$key]['days'][] = $this->getDaysInRange($min_date, $max_date, $day);
        }

        return response()->json(['data' => $data]);
    }

    public function getUsers()
    {
        $users = User::where('setting_role_id', '!=', 14)->select('id', 'first_name', 'last_name',  'setting_role_id')->get();

        return response()->json([
            'users' => $users
        ]);
    }

    public function createBookingTask($user, $receivers, $title, $created_by, $role_id, $date, $actionType, $time, $newDate = null, $newTime = null)
    {
        $created_by_type = 0; // Role
        $status = 0;
        $terminated_by = 0;

        $task_controller = new TaskController();
        $code = $task_controller->generateCode();

        $current_date = Carbon::now()->format('Y-m-d');
        $should_finish = Carbon::parse($current_date)->addDay(1);

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
        $result = (new TaskController)->addReceivers($request_object);
        if ($actionType == "updated"){
            if ($role_id == 14){
                $comment = "<p>La modelo ". $user ." tenía reserva de ". $title ." para la fecha ". $date ." a las " .$time. " y no asistió</p>";
            }else{
                $comment = "<p>El usuario ". $user ." tenía reserva de ". $title ." para la fecha ". $date ." a las " .$time. " y no asistió</p>";
            }
        }else{
            $comment = "<p>Se ha modificado el dia de la reserva de " . $user . " que fue programado inicialmente para la fecha " . $date . " en el horario de " . $time ." Quedando ahora en la fecha " . $newDate . " y horario " . $newTime ."</p>";
        }

        $task_comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => auth()->user()->id,
            'comment' => $comment,
        ]);

        return response()->json([
            'task' => $task,
            'comment' => $task_comment
        ]);
    }

    public function editSchedule($id)
    {
        $schedule = BookingSchedule::findOrFail($id);

        return response()->json([
            'hour' => $schedule->hour,
            'minutes' => $schedule->minutes,
            'meridiem' => $schedule->meridiem
        ]);
    }

    public function updateSchedule($id, Request $request)
    {
        $schedule = BookingSchedule::findOrFail($id);

        $hour = $request->input('hh');
        $minutes = $request->input('mm');
        $meridiem = $request->input('A');

        $exist = BookingSchedule::where([
            ['hour', $hour],
            ['minutes', $minutes],
            ['meridiem', $meridiem],
            ['booking_type_id', $schedule->booking_type_id]
        ])->exists();

        if ($exist){
            $msg = "Ya hay un horario con esta especificacion";
            $icon = "error";
            $code = 403;
        }else{
            $msg = "Horario creado exitosamente!";
            $icon = "success";
            $code = 200;

            $schedule->update([
                'hour' => $hour,
                'minutes' => $minutes,
                'meridiem' => $meridiem
            ]);
        }

        return response()->json([
            'booking' => $schedule,
            'msg' => $msg,
            'icon' => $icon,
            'code' => $code
        ]);
    }

    public function execute()
    {
        /*$min_id = 100;
        $max_id = 200;
        $bookings = DB::connection('gbmedia')->table('psicologia')->whereBetween('psico_id', [$min_id, $max_id])->get();
//        $bookings = DB::connection('gbmedia')->table('au_reserv')->whereBetween('aur_id', [$min_id, $max_id])->get();
        // $bookings = DB::connection('gbmedia')->table('ingles')->whereBetween('ing_id', [$min_id, $max_id])->get();
//         $bookings = DB::connection('gbmedia')->table('maquillaje')->whereBetween('ma_id', [$min_id, $max_id])->get();
        $msg = "nothing bitch!";
        foreach ($bookings as $booking)
        {
            $user = User::where('old_user_id', $booking->fk_u_id)->first();
            $temp = explode('-', $booking->fecha);
            $day = $temp[2];
            $month = $temp[1];
            $year = $temp[0];
            $month_name = date("F", mktime(0, 0, 0, $booking->mes, $booking->dia));
            $date_range = $month_name . "-" . $booking->year;

            $booking_schedule = BookingSchedule::where([
                ['old_sch_id', $booking->horario]
            ])->first();
            
           DB::beginTransaction();
            try
            {
                Booking::updateOrCreate([
                    'old_booking_id' => $booking->aur_id,
                    'booking_schedule_id' => $booking_schedule->id,
                    'booking_type_id' => 1,
                    'user_id' =>$user->id,
                    'model_id' =>$user->id,
                    'rescheduled_by' => NULL,
                    'was_rescheduled' => 0,
                    'nick' => $user->nick,
                    'status' => $booking->estado,
                    'date_range' => $date_range,
                    'date' => $booking->fecha,
                    'day' => $booking->dia,
                    'month' => $booking->mes,
                    'year' => $booking->year,
                    'description' => $booking->descripcion,
                ]);

                //Ingles
                Booking::updateOrCreate([
                    'old_booking_id' => $booking->ing_id,
                    'booking_schedule_id' => $booking_schedule->id,
                    'booking_type_id' => 2,
                    'user_id' =>$user->id,
                    'model_id' =>$user->id,
                    'rescheduled_by' => NULL,
                    'was_rescheduled' => 0,
                    'nick' => $user->nick,
                    'status' => $booking->estado,
                    'date_range' => $date_range,
                    'date' => $booking->fecha,
                    'day' => $booking->dia,
                    'month' => $booking->mes,
                    'year' => $booking->year,
                    'description' => NULL,
                ]);

                //maquillaje
                Booking::updateOrCreate([
                    'old_booking_id' => $booking->ma_id,
                    'booking_schedule_id' => $booking_schedule->id,
                    'booking_type_id' => 3,
                    'user_id' =>$user->id,
                    'model_id' =>$user->id,
                    'rescheduled_by' => NULL,
                    'was_rescheduled' => 0,
                    'nick' => $user->nick,
                    'status' => $booking->estado,
                    'date_range' => $date_range,
                    'date' => $booking->fecha,
                    'day' => $booking->dia,
                    'month' => $booking->mes,
                    'year' => $booking->year,
                    'description' => NULL,
                ]);

                if (is_null($user))
                {
                    continue;
                }
                
                //psichology
                Booking::updateOrCreate([
                    'old_booking_id' => $booking->psico_id,
                    'booking_schedule_id' => $booking_schedule->id,
                    'booking_type_id' => 4,
                    'user_id' =>$user->id,
                    'model_id' =>$user->id,
                    'rescheduled_by' => NULL,
                    'was_rescheduled' => 0,
                    'nick' => $user->nick,
                    'status' => $booking->estado,
                    'date_range' => $date_range,
                    'date' => $booking->fecha,
                    'day' => $booking->dia,
                    'month' => $booking->mes,
                    'year' => $booking->year,
                    'description' => NULL,
                ]);

                DB::commit();

                $msg = "done bitch!";
            }
            catch(\Exception $e)
            {
                DB::rollBack();
                $msg = $e->getMessage();
            }
        }*/

        $min_id = 1000;
        $max_id = 2500;
        $booking_processes = DB::connection('gbmedia')->table('au_proc')->whereBetween('aup_id', [$min_id, $max_id])->get();
        $msg = "nothing bitch!";
        $booking_type_name = "";
        foreach ($booking_processes as $process)
        {
            $user = User::where('old_user_id', $process->fk_u_id_rp)->first();
            if (is_null($user)){
                continue;
            }
            
            if ($user->id === 21 || $user->id === 468 || $user->id === 858 || $user->id === 773 || $user->id === 518)
            {
                $booking_type_name = "fotografia";
            }else{
                $booking_type_name = "video";
            }

            $temp = explode('-', $process->fecha_session);
            $day = $temp[2];
            $month = $temp[1];
            $year = $temp[0];
            $month_name = date("F", mktime(0, 0, 0, $month, $day));
            $date_range = $month_name . "-" . $year;

            DB::beginTransaction();
            try
            {
                $booking = Booking::where('old_booking_id', $process->fk_aur_id)->first();
//                $booking_type = BookingType::where('id', $booking->booking_type_id)->first();
                if ($process->aup_estado === 4 || $process->aup_estado === 2){
                    $status = 2;
                }elseif ($process->aup_estado === 1){
                    $status = 1;
                }elseif ($process->aup_estado === 0){
                    $status = 0;
                }elseif ($process->aup_estado === 3){
                    $status = 0;
                }
                
                BookingProcess::updateOrCreate([
                    'booking_id' => $booking->id,
                    'user_id' => $user->id,
                    'model_id' => $booking->model_id,
                    'booking_type' =>$booking_type_name,
                    'booking_type_id' =>$booking->booking_type_id,
                    'process_status' => $status,
                    'date_range' => $date_range,
                    'session_date' => $process->fecha_session,
                    'submitted_date' => ($process->fecha_editado === '0000-00-00') ? NULL : $process->fecha_editado,
                    'review_date' => ($process->fecha_revisado === '0000-00-00') ? NULL : $process->fecha_revisado,
                    'attachment' => $process->anexo_video
                ]);

                DB::commit();

                $msg = "done bitch!";
            }
            catch(\Exception $e)
            {
                DB::rollBack();
                $msg = $e->getMessage();
            }
        }
        
        return response()->json($msg);
    }

}
