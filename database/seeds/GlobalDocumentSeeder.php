<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GlobalDocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('global_documents')->insert([
            'name' => 'CEDULA CIUDADANIA (C.C)',
            'name_simplified' => 'CC',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('global_documents')->insert([
            'name' => 'CEDULA EXTRANJERIA (C.E)',
            'name_simplified' => 'CE',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('global_documents')->insert([
            'name' => 'NIT',
            'name_simplified' => 'NIT',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('global_documents')->insert([
            'name' => 'PASAPORTE (PAS)',
            'name_simplified' => 'PAS',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('global_documents')->insert([
            'name' => 'Frente Documento',
            'name_simplified' => 'FD',
            'is_listed' => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('global_documents')->insert([
            'name' => 'Reverso Documento',
            'name_simplified' => 'RD',
            'is_listed' => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('global_documents')->insert([
            'name' => 'Rostro - Cédula',
            'name_simplified' => 'RCD',
            'is_listed' => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('global_documents')->insert([
            'name' => 'RUT',
            'name_simplified' => 'RUT',
            'is_listed' => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
        DB::table('global_documents')->insert([
            'name' => 'Permiso de Trabajo',
            'name_simplified' => 'PT',
            'is_listed' => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
