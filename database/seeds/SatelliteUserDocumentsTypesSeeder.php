<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use App\Models\Payrolls\SatelliteUserDocumentsType;
use Illuminate\Support\Facades\DB;

class SatelliteUserDocumentsTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$now = Carbon::now()->format('Y-m-d H:i:s');
        DB::table('satellite_users_documents_types')->insert([
            "name" => "Cedula Ciudadania (Colombia)",
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('satellite_users_documents_types')->insert([
        	"name" => "Cedula Extranjeria (Colombia)",
        	'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('satellite_users_documents_types')->insert([
            "name" => "Pasaporte (Colombia)",
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('satellite_users_documents_types')->insert([
            "name" => "Cedula Extranjera",
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('satellite_users_documents_types')->insert([
            "name" => "Pasaporte Extranjero",
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('satellite_users_documents_types')->insert([
            "name" => "Licencia de Conduccion",
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        DB::table('satellite_users_documents_types')->insert([
            "name" => "Licencia de Conduccion Extranjera",
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
