<?php

use Illuminate\Database\Seeder;
use App\rh_interviews;

class RhEnterviewTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(rh_interviews::class, 20000)->create();
    }
}
