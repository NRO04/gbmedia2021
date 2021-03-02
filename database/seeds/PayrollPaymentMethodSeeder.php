<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use App\Models\Payrolls\PayrollType;
use Illuminate\Support\Facades\DB;

class PayrollPaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$now = Carbon::now()->format('Y-m-d H:i:s');
        DB::table('satellite_payment_methods')->insert([
            "name" => "Ninguno",
            "has_retention" => 0,
        	"pay_with" => 1,
        	'created_at' => $now,
            'updated_at' => $now,
        ]);
        
        DB::table('satellite_payment_methods')->insert([
            "name" => "Banco",
            "has_retention" => 1,
            "pay_with" => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('satellite_payment_methods')->insert([
            "name" => "Efecty",
            "has_retention" => 1,
            "pay_with" => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('satellite_payment_methods')->insert([
            "name" => "Paxum",
            "has_retention" => 0,
            "pay_with" => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('satellite_payment_methods')->insert([
            "name" => "Cheque sin Retencion",
            "has_retention" => 0,
            "pay_with" => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('satellite_payment_methods')->insert([
            "name" => "Banco sin Retencion",
            "has_retention" => 0,
            "pay_with" => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('satellite_payment_methods')->insert([
            "name" => "Banco USA sin Retencion",
            "has_retention" => 0,
            "pay_with" => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('satellite_payment_methods')->insert([
            "name" => "Western Union",
            "has_retention" => 0,
            "pay_with" => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('satellite_payment_methods')->insert([
            "name" => "Banco Regimen Simple",
            "has_retention" => 0,
            "pay_with" => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
