<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HeadquarterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        $miraflores = [
            ['booking_day_id' => 1, 'setting_location_id' => 2, 'booking_type_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['booking_day_id' => 2, 'setting_location_id' => 2, 'booking_type_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['booking_day_id' => 3, 'setting_location_id' => 3, 'booking_type_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['booking_day_id' => 4, 'setting_location_id' => 3, 'booking_type_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['booking_day_id' => 5, 'setting_location_id' => 4, 'booking_type_id' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['booking_day_id' => 6, 'setting_location_id' => 4, 'booking_type_id' => 2, 'created_at' => $now, 'updated_at' => $now],

            ['booking_day_id' => 1, 'setting_location_id' => 3, 'booking_type_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['booking_day_id' => 2, 'setting_location_id' => 3, 'booking_type_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['booking_day_id' => 3, 'setting_location_id' => 2, 'booking_type_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['booking_day_id' => 4, 'setting_location_id' => 2, 'booking_type_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['booking_day_id' => 5, 'setting_location_id' => 4, 'booking_type_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['booking_day_id' => 6, 'setting_location_id' => 4, 'booking_type_id' => 3, 'created_at' => $now, 'updated_at' => $now],
            
            ['booking_day_id' => 1, 'setting_location_id' => 4, 'booking_type_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['booking_day_id' => 2, 'setting_location_id' => 4, 'booking_type_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['booking_day_id' => 3, 'setting_location_id' => 2, 'booking_type_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['booking_day_id' => 4, 'setting_location_id' => 2, 'booking_type_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['booking_day_id' => 5, 'setting_location_id' => 3, 'booking_type_id' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['booking_day_id' => 6, 'setting_location_id' => 3, 'booking_type_id' => 4, 'created_at' => $now, 'updated_at' => $now]
        ];

        DB::table('booking_quarters')->insert($miraflores);
    }
}
