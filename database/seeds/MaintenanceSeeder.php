<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaintenanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        DB::table('maintenance_statuses')->insert([
            'name' => 'Pendiente',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('maintenance_statuses')->insert([
            'name' => 'Verificando',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('maintenance_statuses')->insert([
            'name' => 'Finalizado',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
