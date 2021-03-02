<?php

use Illuminate\Database\Seeder;

class EPSSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = \Carbon\Carbon::now();

        $eps = [
            ['name' => 'Empresas Publicas de Medellin Departamento Medico', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Fondo de Pasivo Social de Ferrocarriles', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Aliansalud EPS', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Salud Total S.A.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Cafesalud EPS', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'E.P.S Sanitas', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Compensar Entidad Promotora de Salud', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'EPS Sura', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Comfenalco Valle EPS', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Coomeva EPS', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Famisanar', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Servicio Occidental de Salud S.O.S. S.A.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Cruz Blanca S.A', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Saludvida S.A EPS', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Nueva EPS', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Fondo de Solidaridad y Garantia Fosyga', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'EMSSANAR', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'MALLAMAS', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'MEDIMAS EPS', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'COOSALUD EPS', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'ASMET SALUD EPS', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'COMPARTA EPS', 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('global_eps')->insert($eps);
    }
}
