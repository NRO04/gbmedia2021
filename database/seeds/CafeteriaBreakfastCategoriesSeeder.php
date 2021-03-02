<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CafeteriaBreakfastCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cafeteria_breakfast_categories')->insert([
            'name' => 'Huevos',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('cafeteria_breakfast_categories')->insert([
            'name' => 'Frutas',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('cafeteria_breakfast_categories')->insert([
            'name' => 'Acompañantes',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('cafeteria_breakfast_categories')->insert([
            'name' => 'Bebidas',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('cafeteria_breakfast_categories')->insert([
            'name' => 'Adicionales',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
