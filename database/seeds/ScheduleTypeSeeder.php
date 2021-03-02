<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ScheduleTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$now = Carbon::now()->format('Y-m-d H:i:s');

        DB::table('schedule_session_types')->insert([
        	'name' => 'Mañana',
        	'created_at' => $now,
        	'updated_at' => $now,
        ]);

        DB::table('schedule_session_types')->insert([
        	'name' => 'Tarde',
        	'created_at' => $now,
        	'updated_at' => $now,
        ]);

        DB::table('schedule_session_types')->insert([
        	'name' => 'Media Tarde',
        	'created_at' => $now,
        	'updated_at' => $now,
        ]);

        DB::table('schedule_session_types')->insert([
        	'name' => 'Noche',
        	'created_at' => $now,
        	'updated_at' => $now,
        ]);
    }
}
