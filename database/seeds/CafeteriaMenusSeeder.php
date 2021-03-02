<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CafeteriaMenusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cafeteria_menus')->insert([
            'description' => 'Crema de Maiz + Filete de pollo + Ensalada + Arroz + Jugo',
            'cafeteria_type_id' => 2,
            'price' => 6500,
            'date' => '2020-10-06',
            'created_by' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('cafeteria_menus')->insert([
            'description' => 'Filete de pollo + Papas',
            'cafeteria_type_id' => 3,
            'price' => 4500,
            'date' => '2020-10-05',
            'created_by' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
