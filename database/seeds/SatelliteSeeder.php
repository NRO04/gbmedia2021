<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SatelliteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');
        
        //payment owner info
        for ($i=1; $i <= 40 ; $i++) { 
            $owner_payment_info = [
                ['owner' => $i, 'created_at' => $now, 'updated_at' => $now],
            ];

            DB::table('satellite_owners_payment_info')->insert($owner_payment_info);
        }
        
    }
}
