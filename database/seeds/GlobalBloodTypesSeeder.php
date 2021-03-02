<?php
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GlobalBloodTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        $blood_types = [
            ['name' => 'A+', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'B+', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'O+', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'AB+', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'A-', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'B-', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'O-', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'AB-', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('global_blood_types')->insert($blood_types);
    }
}
