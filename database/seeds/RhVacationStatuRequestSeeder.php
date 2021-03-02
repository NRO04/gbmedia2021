<?php

use Illuminate\Database\Seeder;

class RhVacationStatuRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rh_vacation_status = [
            ['status' => 'process'],
            ['status' => 'approved'],
            ['status' => 'disapproved'],
        ];

        DB::table('rh_vacation_status')->insert($rh_vacation_status);
    }
}
