<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use App\Models\Payrolls\PayrollType;
use Illuminate\Support\Facades\DB;

class PayrollTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$now = Carbon::now()->format('Y-m-d H:i:s');
        DB::table('payroll_types')->insert([
        	"name" => "Recargo Nocturno",
        	'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('payroll_types')->insert([
        	"name" => "Comisiones",
        	'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('payroll_types')->insert([
        	"name" => "Auxilio Movilizacion",
        	'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('payroll_types')->insert([
        	"name" => "Re-Record",
        	'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('payroll_types')->insert([
        	"name" => "Bonificacion",
        	'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('payroll_types')->insert([
        	"name" => "Auxilio Transporte",
        	'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('payroll_types')->insert([
        	"name" => "Cafeteria",
        	'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('payroll_types')->insert([
        	"name" => "Nevera",
        	'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('payroll_types')->insert([
        	"name" => "Otros",
        	'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('payroll_types')->insert([
        	"name" => "LLegada Tarde",
        	'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('payroll_types')->insert([
        	"name" => "Boutique",
        	'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('payroll_types')->insert([
            "name" => "Seguridad Social",
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('payroll_types')->insert([
            "name" => "Bonificacion Extra",
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('payroll_types')->insert([
            "name" => "Hora Extra",
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('payroll_types')->insert([
            "name" => "Prestamo",
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
