<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StudiosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('studios')->insert([
            'studio_name' => 'GB Media Group',
            'url' => 'https://gbmediagroup.com/admin/admin/login/gb',
            'db_name' => 'gbmediag_admin',
            'db_user' => 'root',
            'db_passcode' => '',
            'status' => 1,
            'owner_studio_id' => 3,
            'rooms_control_code' => '1001',
            'unique_code' => '2dIDnwmrgoZxNlm',
            'support_db_name' => 'gbmediag_support',
            'support_db_user' => 'gbmediag_admin',
            'support_db_passcode' => '4JGPsE3ehX-v',
            'support_url' => 'https://soporte.gbmediagroup.com',
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        /*DB::table('studios')->insert([
            'studio_name' => 'Trend',
            'url' => 'https://gbmediagroup.com/admin/admin/login/trend',
            'db_name' => 'trend',
            'db_user' => 'root',
            'db_passcode' => '',
            'status' => 1,
            'owner_studio_id' => 3,
            'rooms_control_code' => '1001',
            'unique_code' => '2dIDnwmrgoZxNlm1',
            'support_db_name' => 'trend_support',
            'support_db_user' => 'trend_admin',
            'support_db_passcode' => '4JGPsE3ehX-v',
            'support_url' => 'https://soporte.trendstudios.co',
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);*/
    }
}
