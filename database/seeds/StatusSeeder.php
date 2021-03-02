<?php

use App\Models\Globals\GlobalStatus;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');
        
        $statuses = [
            ['status' => 'Pendiente', 'value' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['status' => 'Asistió', 'value' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['status' => 'No asistió', 'value' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['status' => 'Deshabilitado', 'value' => 4, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('global_statuses')->insert($statuses);
    }
}
