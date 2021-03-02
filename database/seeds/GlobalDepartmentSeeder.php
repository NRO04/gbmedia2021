<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class GlobalDepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        $Departments = [
            ['name' =>'ANTIOQUIA', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'ATLÁNTICO', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'BOGOTÁ, D.C.', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'BOLÍVAR', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'BOYACÁ', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'CALDAS', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'CAQUETÁ', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'CAUCA', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'CESAR', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'CÓRDOBA', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'CUNDINAMARCA', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'CHOCÓ', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'HUILA', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'LA GUAJIRA', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'MAGDALENA', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'META', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'NARIÑO', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'NORTE DE SANTANDER', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'QUINDIO', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'RISARALDA', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'SANTANDER', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'SUCRE', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'TOLIMA', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'VALLE DEL CAUCA', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'ARAUCA', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'CASANARE', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'PUTUMAYO', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'ARCHIPIÉLAGO DE SAN ANDRÉS, PROVIDENCIA Y SANTA CATALINA', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'AMAZONAS', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'GUAINÍA', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'GUAVIARE', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'VAUPÉS', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'VICHADA', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('global_departments')->insert($Departments);
    }
}
