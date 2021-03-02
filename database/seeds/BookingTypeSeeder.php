<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BookingTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        $bookingType = [
            ['booking' => 'Audiovisuales', 'type' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['booking' => 'Ingles', 'type' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['booking' => 'Maquillaje', 'type' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['booking' => 'Psicologia', 'type' => 4, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('booking_types')->insert($bookingType);

        $bookingDays = [
            ['day_name' => 'Lunes', 'created_at' => $now, 'updated_at' => $now],
            ['day_name' => 'Martes', 'created_at' => $now, 'updated_at' => $now],
            ['day_name' => 'Miercoles', 'created_at' => $now, 'updated_at' => $now],
            ['day_name' => 'Jueves', 'created_at' => $now, 'updated_at' => $now],
            ['day_name' => 'Viernes', 'created_at' => $now, 'updated_at' => $now],
            ['day_name' => 'Sabado', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('booking_days')->insert($bookingDays);
    }
}
