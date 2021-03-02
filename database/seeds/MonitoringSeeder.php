<?php

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MonitoringSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $week_start = Carbon::now()->startOfWeek(Carbon::SUNDAY)->toDateString();
        $week_end = Carbon::now()->endOfWeek(Carbon::SATURDAY)->toDateString();
        $users = User::select('id', 'setting_location_id', 'nick')->where('setting_role_id', 14)->get();

        foreach ($users as $user){
            $monitoring = [
                [
                    'model_id' => $user->id,
                    'monitor_id' => NULL,
                    'range' => $week_start." / ".$week_end,
                    'date' => Carbon::now()->format('Y-m-d'),
                    'status' => 0,
                    'setting_location_id' => $user->setting_location_id,
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
                ]
            ];
            DB::table('monitoring')->insert($monitoring);
        }

        $now = Carbon::now()->format('Y-m-d H:i:s');
        $statuses = [
            ['answer' => 'Bien', 'value' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['answer' => 'Mal', 'value' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['answer' => 'Regular', 'value' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['answer' => 'Si', 'value' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['answer' => 'No', 'value' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['answer' => 'A veces', 'value' => 6, 'created_at' => $now, 'updated_at' => $now],
            ['answer' => 'Divertido', 'value' => 7, 'created_at' => $now, 'updated_at' => $now],
            ['answer' => 'Aburrido', 'value' => 8, 'created_at' => $now, 'updated_at' => $now]
        ];
        DB::table('monitoring_status')->insert($statuses);
    }
}
