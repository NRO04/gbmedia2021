<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AlarmsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        DB::table('alarm_status')->insert([
            'name' => 'Pendiente',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('alarm_status')->insert([
            'name' => 'Finalizado',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('alarms')->insert([
            'name' => 'Formateo Computadores PH y Bodega',
            'status_id' => 1,
            'showing_date' => '2020-07-20',
            'cycle_count' => 1,
            'cycle' => 'weekly',
            'is_fixed_date' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('alarms')->insert([
            'name' => 'SSL grupo-bedoya.com',
            'status_id' => 2,
            'showing_date' => '2020-07-27',
            'cycle_count' => 1,
            'cycle' => 'monthly',
            'finished_by' => 1,
            'finished_date' => $now,
            'is_fixed_date' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('alarms')->insert([
            'name' => 'Fiesta de Navidad',
            'status_id' => 2,
            'showing_date' => '2020-09-01',
            'cycle_count' => 1,
            'cycle' => 'yearly',
            'is_fixed_date' => 1,
            'finished_by' => 2,
            'finished_date' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('alarm_users')->insert([
            'alarm_id' => 1,
            'user_id' => 1,
        ]);

        DB::table('alarm_users')->insert([
            'alarm_id' => 1,
            'user_id' => 2,
        ]);

        DB::table('alarm_users')->insert([
            'alarm_id' => 1,
            'user_id' => 10,
        ]);

        DB::table('alarm_users')->insert([
            'alarm_id' => 2,
            'user_id' => 1,
        ]);

        DB::table('alarm_users')->insert([
            'alarm_id' => 2,
            'user_id' => 12,
        ]);
    }
}
