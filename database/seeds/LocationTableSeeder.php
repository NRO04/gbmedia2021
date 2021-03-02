<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        DB::table('setting_locations')->insert([
            'name' => 'All',
            'rooms' => 0,
            'position' => 0,
            'base' => 0,
            'address' => "",
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('setting_locations')->insert([
            'name' => 'Penthouse',
            'rooms' => 12,
            'position' => 0,
            'base' => 0,
            'address' => "",
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('setting_locations')->insert([
            'name' => 'Bodega',
            'rooms' => 8,
            'position' => 0,
            'base' => 1,
            'address' => "",
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('setting_locations')->insert([
            'name' => 'Miraflores',
            'rooms' => 8,
            'position' => 0,
            'base' => 0,
            'address' => "",
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('setting_location_permissions')->insert([
            'setting_location_id' => 1,
            'location_id' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
