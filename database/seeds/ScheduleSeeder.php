<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');
        $audiovisual = [
            ['hour' => '09', 'minutes' => '00', 'meridiem' => 'AM', 'booking_type_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['hour' => '01', 'minutes' => '00', 'meridiem' => 'PM', 'booking_type_id' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['hour' => '05', 'minutes' => '00', 'meridiem' => 'PM', 'booking_type_id' => 1, 'created_at' => $now, 'updated_at' => $now],
   
            ['hour' => '08', 'minutes' => '00', 'meridiem' => 'AM', 'booking_type_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['hour' => '10', 'minutes' => '00', 'meridiem' => 'AM', 'booking_type_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['hour' => '01', 'minutes' => '00', 'meridiem' => 'PM', 'booking_type_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['hour' => '03', 'minutes' => '00', 'meridiem' => 'PM', 'booking_type_id' => 2, 'created_at' => $now, 'updated_at' => $now],

            ['hour' => '07', 'minutes' => '00', 'meridiem' => 'AM', 'booking_type_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['hour' => '11', 'minutes' => '00', 'meridiem' => 'AM', 'booking_type_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['hour' => '03', 'minutes' => '00', 'meridiem' => 'PM', 'booking_type_id' => 3, 'created_at' => $now, 'updated_at' => $now],

            ['hour' => '09', 'minutes' => '00', 'meridiem' => 'AM', 'booking_type_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['hour' => '10', 'minutes' => '00', 'meridiem' => 'AM', 'booking_type_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['hour' => '11', 'minutes' => '00', 'meridiem' => 'AM', 'booking_type_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['hour' => '01', 'minutes' => '00', 'meridiem' => 'PM', 'booking_type_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['hour' => '02', 'minutes' => '00', 'meridiem' => 'PM', 'booking_type_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['hour' => '03', 'minutes' => '00', 'meridiem' => 'PM', 'booking_type_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['hour' => '04', 'minutes' => '00', 'meridiem' => 'PM', 'booking_type_id' => 4, 'created_at' => $now, 'updated_at' => $now],
        ];
        DB::table('booking_schedules')->insert($audiovisual);
    }
}
