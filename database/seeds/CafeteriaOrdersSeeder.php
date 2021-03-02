<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CafeteriaOrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cafeteria_orders')->insert([
            'user_id' => 1,
            'cafeteria_menu_id' => 1,
            'location_id' => 2,
            'quantity' => 1,
            'total' => 6500,
            'date' => '2020-10-06',
            'payment_date' => '2020-10-06',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('cafeteria_orders')->insert([
            'observations' => 'Sin papas',
            'user_id' => 1,
            'cafeteria_menu_id' => 2,
            'location_id' => 2,
            'quantity' => 1,
            'total' => 4500,
            'date' => '2020-10-05',
            'payment_date' => '2020-10-05',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
