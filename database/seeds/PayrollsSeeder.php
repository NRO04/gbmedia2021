<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use App\Models\Payrolls\Payroll;
use Illuminate\Support\Facades\DB;

class PayrollsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$now = Carbon::now()->format('Y-m-d H:i:s');

        DB::table('payrolls')->insert([
        	"user_id" => 1,
        	"month" => 11,
        	"year" => 2020,
        	"salary1" => 1500000,
        	"worked_days1" => 15,
        	"salary2" => 1500000,
        	"worked_days2" => 15,
        	'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('payrolls')->insert([
        	"user_id" => 2,
        	"month" => 11,
        	"year" => 2020,
        	"salary1" => 2000000,
        	"worked_days1" => 15,
        	"salary2" => 2000000,
        	"worked_days2" => 15,
        	'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('payrolls')->insert([
        	"user_id" => 3,
        	"month" => 11,
        	"year" => 2020,
        	"salary1" => 2100000,
        	"worked_days1" => 15,
        	"salary2" => 2100000,
        	"worked_days2" => 15,
        	'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('payrolls')->insert([
        	"user_id" => 4,
        	"month" => 11,
        	"year" => 2020,
        	"salary1" => 1500000,
        	"worked_days1" => 15,
        	"salary2" => 1500000,
        	"worked_days2" => 15,
        	'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('payrolls')->insert([
        	"user_id" => 5,
        	"month" => 11,
        	"year" => 2020,
        	"salary1" => 1500000,
        	"worked_days1" => 15,
        	"salary2" => 1500000,
        	"worked_days2" => 15,
        	'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('payrolls')->insert([
        	"user_id" => 1,
        	"month" => 12,
        	"year" => 2020,
        	"salary1" => 1600000,
        	"worked_days1" => 15,
        	"salary2" => 1600000,
        	"worked_days2" => 15,
        	'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('payrolls')->insert([
        	"user_id" => 2,
        	"month" => 12,
        	"year" => 2020,
        	"salary1" => 2000000,
        	"worked_days1" => 15,
        	"salary2" => 2000000,
        	"worked_days2" => 15,
        	'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('payrolls')->insert([
        	"user_id" => 3,
        	"month" => 12,
        	"year" => 2020,
        	"salary1" => 2100000,
        	"worked_days1" => 15,
        	"salary2" => 2100000,
        	"worked_days2" => 15,
        	'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('payrolls')->insert([
        	"user_id" => 4,
        	"month" => 12,
        	"year" => 2020,
        	"salary1" => 1600000,
        	"worked_days1" => 15,
        	"salary2" => 1600000,
        	"worked_days2" => 15,
        	'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('payrolls')->insert([
        	"user_id" => 5,
        	"month" => 12,
        	"year" => 2020,
        	"salary1" => 1600000,
        	"worked_days1" => 15,
        	"salary2" => 1600000,
        	"worked_days2" => 15,
        	'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
