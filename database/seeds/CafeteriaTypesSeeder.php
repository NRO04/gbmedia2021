<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CafeteriaTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cafeteria_types')->insert([
            'name' => 'Desayuno',
            'time' => '08:30:00',
            'max_order_time' => '08:10:00',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('cafeteria_types')->insert([
            'name' => 'Almuerzo',
            'time' => '12:30:00',
            'max_order_time' => '10:30:00',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('cafeteria_types')->insert([
            'name' => 'Refrigerio',
            'time' => '18:00:00',
            'max_order_time' => '16:30:00',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('cafeteria_types')->insert([
            'name' => 'Trasnocho',
            'time' => '23:00:00',
            'max_order_time' => '20:00:00',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
