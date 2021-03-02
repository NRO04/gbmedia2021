<?php

namespace App\Console\Commands\EverySunday;

use App\Models\Attendance\AttendanceComment;
use App\Models\Attendance\AttendanceSummary;
use App\Models\Attendance\Attendance as ModelAttendance;
use App\Models\Satellite\SatelliteAccount;
use App\Models\Schedule\Schedule;
use App\Models\Statistics\Statistics;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class attendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:attendance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command would create attendanace summary for all models every sunday';

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
        $this->info('******** Started cron');
        $models = User::where('setting_role_id', 14)->where('status', 1)->get();
        $root = 594;
        $count_goal = 0;
        $attendance_range = NULL;

        foreach ($models as $model) {

            $schedules = Schedule::where('user_id', $model->id)->get();
            $model_goal = AttendanceSummary::select('goal')->where('model_id', $model->id)->first();
            if (!is_null($model_goal)) {
                $goal = $model_goal->goal;
            } else {
                $goal = 50.00;
            }

            foreach ($schedules as $schedule) {
                if (!is_null($schedule)) {
                    $yesterday = Carbon::yesterday()->format('Y-m-d');
                    $from = Carbon::parse($yesterday)->startOfWeek(Carbon::SUNDAY)->toDateString();
                    $to = Carbon::parse($yesterday)->endOfWeek(Carbon::SATURDAY)->toDateString();
                    $range = $from . " / " . $to;
                    // search for amount of mins owed from past week
                    $summary = AttendanceSummary::where('model_id', $model->id)->where('range', $range)->first();

                    // search for the models goal during past week and increases it
                    for ($d = Carbon::parse($from); $d->lte($to); $d->addDay()) {
                        $s = Statistics::select(DB::raw("SUM(value) as total_amount"))->where([['user_id', '=', $model->id], ['date', '=', $d->format('Y-m-d')]])->first();
                        if ($s->total_amount >= $goal) {
                            $count_goal++;

                            if ($count_goal >= 4) {
                                $goal = $goal + 50.00;
                            }
                        }
                    }

                    //Inserts the attendance summary from the starting week
                    $today = Carbon::today()->format('Y-m-d');
                    $desde = Carbon::parse($today)->startOfWeek(Carbon::SUNDAY)->toDateString();
                    $upto = Carbon::parse($today)->endOfWeek(Carbon::SATURDAY)->toDateString();
                    $week_range = $desde . " / " . $upto;
                    $at_summary = AttendanceSummary::updateOrCreate([
                        'model_id' => $model->id,
                        'setting_location_id' => $model->setting_location_id,
                        'range' => $week_range,
                        'date' => $today,
                        'worked_days' => 0,
                        'unjustified_days' => 0,
                        'justified_days' => 0,
                        'period' => 0,
                        'total_minutes' => !is_null($summary) ? $summary->total_minutes : 0,
                        'total_recovery_minutes' => 0,
                        'goal' => $goal,
                        'created_by' => $root,
                    ]);

                    // Inserts resting days of the model during the week on attendance table

                    $today = Carbon::today()->format('Y-m-d');
                    $at_from = Carbon::parse($today)->startOfWeek(Carbon::SUNDAY)->toDateString();
                    $at_to = Carbon::parse($today)->endOfWeek(Carbon::SATURDAY)->toDateString();

                    $attendance_range = $at_from . " / " . $at_to;

                    // $restDays = ($this->getModelRestDay($schedule, $today)) !== null ? $this->getModelRestDay($schedule, $today) : [];
                    $schedule_temp = [
                        'mon' => $schedule->mon,
                        'tue' => $schedule->tue,
                        'wed' => $schedule->wed,
                        'thu' => $schedule->thu,
                        'fri' => $schedule->fri,
                        'sat' => $schedule->sat,
                        'sun' => $schedule->sun
                    ]; //Horario de una modelo

                    $restDays = ($this->getModelRestDay($schedule_temp, $today));

                    // Obtiene las fechas que la modelo descansa.

                    foreach ($restDays as $restDay) {

                        $model_attendance = ModelAttendance::updateOrCreate([
                            'attendance_summary_id' => $at_summary->id,
                            'model_id' => $model->id,
                            'setting_location_id' => $model->setting_location_id,
                            'range' => $attendance_range,
                            'date' => $restDay,
                            'attendance_type' => 8,
                            'attendance_minutes' => 0,
                            'recovery_minutes' => 0,
                            'created_by' => $root,
                        ]);

                        $at_comment = AttendanceComment::updateOrCreate([
                            'attendance_id' => $model_attendance->id,
                            'attendance_status_id' => 8,
                            'created_by' => $root,
                            'comment' => "La modelo " . $model->nick . " se encuentra en su dia de descanso",
                        ]);
                    }
                }
            }

            // Starts statistics in NULL for all the models
            // $accounts = SatelliteAccount::where('user_id', $model->id)->where('from_gb', 1)->get();
            // foreach ($accounts as $account) {
            //     Statistics::updateOrCreate([
            //         'satellite_account_id' => $account->id,
            //         'user_id' => $model->id,
            //         'setting_page_id' => $account->page_id,
            //         'setting_location_id' => $model->setting_location_id,
            //         'value' => NULL,
            //         'range' => $attendance_range,
            //         'date' => Carbon::now()->format('Y-m-d'),
            //     ]);
            // }
        }

        $this->info('****** Finish cron');
    }


    /**
     *
     *Retorna los dias que descansa una modelo.
     *@var schedule
     * Recibe el horario de la modelo y la fecha actual.
     */
    public function getModelRestDay($schedule, $startOfTheWeek)
    {
        $restDays = []; //Array que guarda las fechas que la modelo descansa.

        $weekDays = [
            'mon' => 'MONDAY',
            'tue' => 'TUESDAY',
            'wed' => 'WEDNESDAY',
            'thu' => 'THURSDAY',
            'fri' => 'FRIDAY',
            'sat' => 'SATURDAY',
            'sun' => 'SUNDAY',

        ]; // Dias de la semana
        $propertiesWeekDays = array_keys($weekDays); // Obtiene las propiedades de los dias de la semana abreviados. Guarda directamente las llaves del array asociativo.

        for ($i = 0; $i < count($schedule); $i++) {

            $key = $propertiesWeekDays[$i]; // Nombre de dia de la semana abreviado ej: 'mon'

            $dateStr = 'next ' . strtolower($weekDays[$key]); //Dia en string.

            $restDayDate =  date('Y-m-d', strtotime($dateStr, strtotime($startOfTheWeek))); //Obtiene la fecha de la siguiente semana segun el dia especificado.

            /**
             * Se muta el array especificando la llave a buscar. Se le asigna directamente la propiedad a traves de $key, si esta es igual, es posible acceder a ella
             * y obtener su valor.
             * Ej: $key = 'mon';
             * ej: $schedule[$key] == $schedule['mon']
             *de esta manera se obtiene directamente el valor para
             *Posteriormente validar si cumple con una condicion, ej: si el valor es = 3 quiere decir que ese dia la modelo descansa.
             */

            if ($schedule[$key] == 3) {
                array_push($restDays, $restDayDate); // Se guarda la fecha en el array.
            }

            // echo ($schedule[$key] == 3) ? "Descansa".$restDayDate."   " : "Trabaja ".$restDayDate."   "; //
        }

        return $restDays; // Retorna dias que la modelo descansa.
    }
}
