<?php

use Illuminate\Database\Seeder;

class RhExtraValueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('rh_extra_values')->insert([
            'day_value' => 3500,
            'night_value' => 4000,
        ]);
    }
}
