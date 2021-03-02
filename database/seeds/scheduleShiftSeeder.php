<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class scheduleShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');
        $sessions = [
            [
                'session' => 1,
                'setting_location_id' => 2,
                'available' => 10,
                'shift_start' => '06:00 am',
                'shift_end' => '14:00 pm',
                'working_time' => 480,
                'break' => 30,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'session' => 2,
                'setting_location_id' => 2,
                'available' => 10,
                'shift_start' => '14:00 pm',
                'shift_end' => '10:00 pm',
                'working_time' => 480,
                'break' => 30,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'session' => 3,
                'setting_location_id' => 2,
                'available' => 10,
                'shift_start' => '14:00 pm',
                'shift_end' => '10:00 pm',
                'working_time' => 480,
                'break' => 30,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'session' => 4,
                'setting_location_id' => 2,
                'available' => 10,
                'shift_start' => '18:00 pm',
                'shift_end' => '02:00 am',
                'working_time' => 480,
                'break' => 30,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'session' => 1,
                'setting_location_id' => 3,
                'available' => 10,
                'shift_start' => '06:00 am',
                'shift_end' => '14:00 pm',
                'working_time' => 480,
                'break' => 30,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'session' => 2,
                'setting_location_id' => 3,
                'available' => 10,
                'shift_start' => '14:00 pm',
                'shift_end' => '10:00 pm',
                'working_time' => 480,
                'break' => 30,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'session' => 3,
                'setting_location_id' => 3,
                'available' => 10,
                'shift_start' => '14:00 pm',
                'shift_end' => '10:00 pm',
                'working_time' => 480,
                'break' => 30,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'session' => 4,
                'setting_location_id' => 3,
                'available' => 10,
                'shift_start' => '18:00 pm',
                'shift_end' => '02:00 am',
                'working_time' => 480,
                'break' => 30,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'session' => 1,
                'setting_location_id' => 4,
                'available' => 10,
                'shift_start' => '06:00 am',
                'shift_end' => '14:00 pm',
                'working_time' => 480,
                'break' => 30,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'session' => 2,
                'setting_location_id' => 4,
                'available' => 10,
                'shift_start' => '14:00 pm',
                'shift_end' => '10:00 pm',
                'working_time' => 480,
                'break' => 30,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'session' => 3,
                'setting_location_id' => 4,
                'available' => 10,
                'shift_start' => '14:00 pm',
                'shift_end' => '10:00 pm',
                'working_time' => 480,
                'break' => 30,
                'created_at' => $now,
                'updated_at' => $now
            ],
            [
                'session' => 4,
                'setting_location_id' => 4,
                'available' => 10,
                'shift_start' => '18:00 pm',
                'shift_end' => '02:00 am',
                'working_time' => 480,
                'break' => 30,
                'created_at' => $now,
                'updated_at' => $now
            ]
        ];
        DB::table('schedule_sessions')->insert($sessions);
    }
}
