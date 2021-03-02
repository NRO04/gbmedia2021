<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SettingPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');
        
        $pages = [
            ['name' =>'Jasmin', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'Streamate', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'Chaturbate', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'Camsoda', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'Bongacams', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'Flirt4Free', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'StripChat', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'ImLive', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'XLoveCam', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'Eurolive', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'MyKoCam', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'FireCams', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'Cams', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'Cam4', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'Eplay', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'Xvrchat', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'SkyPrivate', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'OleCams', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('setting_pages')->insert($pages);
    }
}
