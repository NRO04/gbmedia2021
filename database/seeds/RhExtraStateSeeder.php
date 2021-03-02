<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class RhExtraStateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');
        DB::table('rh_extra_state')->insert([
        	"name" => "process",
        	'created_at' => $now,
            'updated_at' => $now,
        ]);

        $now = Carbon::now()->format('Y-m-d H:i:s');
        DB::table('rh_extra_state')->insert([
        	"name" => "approved",
        	'created_at' => $now,
            'updated_at' => $now,
        ]);

        $now = Carbon::now()->format('Y-m-d H:i:s');
        DB::table('rh_extra_state')->insert([
        	"name" => "disapproved",
        	'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
