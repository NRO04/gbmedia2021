<?php

use App\Models\Attendance\Attendance;
use App\Models\Attendance\AttendanceSummary;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

use App\User;
use Carbon\Carbon;
use Faker\Generator as Faker;

class AttendanceStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run(Faker $faker)
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');
        $statuses = [
            ['status' => 'Llegada', 'value' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['status' => 'Conexion', 'value' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['status' => 'Periodo', 'value' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['status' => 'Enferma', 'value' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['status' => 'Falta justificada', 'value' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['status' => 'Falta injustificada', 'value' => 6, 'created_at' => $now, 'updated_at' => $now],
            ['status' => 'Desconexion', 'value' => 7, 'created_at' => $now, 'updated_at' => $now],
            ['status' => 'Descanso', 'value' => 8, 'created_at' => $now, 'updated_at' => $now],
            ['status' => 'Vacaciones', 'value' => 9, 'created_at' => $now, 'updated_at' => $now],
            ['status' => 'Pausa', 'value' => 10, 'created_at' => $now, 'updated_at' => $now],
            ['status' => 'Trabaja dia libre', 'value' => 11, 'created_at' => $now, 'updated_at' => $now],
            ['status' => 'Tiempo extra', 'value' => 12, 'created_at' => $now, 'updated_at' => $now],
            ['status' => 'Doble turno', 'value' => 13, 'created_at' => $now, 'updated_at' => $now],
            ['status' => 'Error cometido', 'value' => 14, 'created_at' => $now, 'updated_at' => $now],
            ['status' => 'Quitar día injustificado', 'value' => 15, 'created_at' => $now, 'updated_at' => $now],
        ];
        DB::table('attendance_statuses')->insert($statuses);

        $week_start = Carbon::now()->startOfWeek(Carbon::SUNDAY)->toDateString();
        $week_end = Carbon::now()->endOfWeek(Carbon::SATURDAY)->toDateString();
        $users = User::select('id', 'setting_location_id')->where('setting_role_id', 14)->get();
        $range = $week_start." / ".$week_end;
        $from = Carbon::parse($week_start);
        $to = Carbon::parse($week_end);

        foreach ($users as $user){
            $summary = [
                [
                    'model_id' => $user->id,
                    'range' => $range,
                    'date' => Carbon::now()->format('Y-m-d'),
                    'worked_days' => 0,
                    'unjustified_days' => 0,
                    'justified_days' => 0,
                    'period' => 0,
                    'total_minutes' => 0,
                    'total_recovery_minutes' => 0,
                    'created_by' => 1,
                    'created_at' => $now,
                    'updated_at' => $now
                ],
            ];
            DB::table('attendance_summary')->insert($summary);
        }

        foreach ($users as $user){

            $schedules = [
                [
                    'user_id' => $user->id,
                    'mon' => $faker->numberBetween($min = 1, $max = 4),
                    'tue' => $faker->numberBetween($min = 1, $max = 4),
                    'wed' => $faker->numberBetween($min = 1, $max = 4),
                    'thu' => $faker->numberBetween($min = 1, $max = 4),
                    'fri' => $faker->numberBetween($min = 1, $max = 4),
                    'sat' => $faker->numberBetween($min = 1, $max = 4),
                    'sun' => $faker->numberBetween($min = 1, $max = 4),
                    'setting_location_id' => $user->setting_location_id,
                    'session' => $faker->numberBetween($min = 1, $max = 4),
                    'created_at' => $now,
                    'updated_at' => $now
                ],
            ];
            DB::table('schedules')->insert($schedules);
        }

        $array = [1, 3, 4, 5, 6, 8];

       /*for($d = $from; $d->lte($to); $d->addDay()) {
            foreach ($users as $user){
                $summary_id = AttendanceSummary::where([['model_id', '=', $user->id], ['range', '=', $range]])->first();

                $attendances = [
                    [
                        'attendance_summary_id' => $summary_id->id,
                        'model_id' => $user->id,
                        'range' => $range,
                        'date' => $d->format('Y-m-d'),
                        'attendance_type' => Arr::random($array),
                        'attendance_minutes' => $faker->numberBetween($min = 1, $max = 480),
                        'recovery_minutes' => 0,
                        'created_by' => 56,
                        'created_at' => $now,
                        'updated_at' => $now
                    ],
                ];
                DB::table('attendances')->insert($attendances);
            }
        }*/


        $last_week_start ="2020-12-13";
        $last_week_end = "2020-12-19";
        $desde = Carbon::parse($last_week_start);
        $hasta = Carbon::parse($last_week_end);
        $rango = $last_week_start." / ".$last_week_end;

        foreach ($users as $user){
            $summary = [
                [
                    'model_id' => $user->id,
                    'range' => $rango,
                    'date' => '2020-12-13',
                    'worked_days' => 0,
                    'unjustified_days' => 0,
                    'justified_days' => 0,
                    'period' => 0,
                    'total_minutes' => 0,
                    'total_recovery_minutes' => 0,
                    'goal' => 50.00,
                    'created_by' => 1,
                    'created_at' => $now,
                    'updated_at' => $now
                ],
            ];
            DB::table('attendance_summary')->insert($summary);
        }

        for($dia = $desde; $dia->lte($hasta); $dia->addDay()) {
            foreach ($users as $user){
                $summary_id = AttendanceSummary::where([['model_id', '=', $user->id], ['range', '=', $rango]])->first();

                $attendances = [
                    [
                        'attendance_summary_id' => $summary_id->id,
                        'model_id' => $user->id,
                        'range' => $rango,
                        'date' => $dia->format('Y-m-d'),
                        'attendance_type' => Arr::random($array),
                        'attendance_minutes' => $faker->numberBetween($min = 1, $max = 480),
                        'recovery_minutes' => 0,
                        'created_by' => 56,
                        'created_at' => $now,
                        'updated_at' => $now
                    ],
                ];
                DB::table('attendances')->insert($attendances);
            }
        }

        foreach ($users as $user){
            $summary_id = AttendanceSummary::where('model_id', '=', $user->id)->first();

            $attendances = [
                [
                    'attendance_summary_id' => $summary_id->id,
                    'model_id' => $user->id,
                    'range' => $week_start." / ".$week_end,
                    'date' => Carbon::now()->format('Y-m-d'),
                    'attendance_type' => 1,
                    'attendance_minutes' => 0,
                    'recovery_minutes' => 0,
                    'created_by' => 56,
                    'created_at' => $now,
                    'updated_at' => $now
                ],
            ];
            DB::table('attendances')->insert($attendances);
        }

        $attendances = Attendance::all();
        $count = 1;
        foreach ($attendances as $attendance){
            $comments = [
                [
                    'attendance_id' => $attendance->id,
                    'attendance_status_id' => $attendance->attendance_type,
                    'created_by' => 56,
                    'comment' => "Some random comment because I am seeding this info ".$count,
                    'created_at' => $now,
                    'updated_at' => $now
                ],
            ];
            DB::table('attendance_comments')->insert($comments);
            $count++;
        }
    }
}
