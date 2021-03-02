<?php
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GlobalTypeContractSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        DB::table('global_type_contracts')->insert([
            'name' => 'Prestación de Servicios',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('global_type_contracts')->insert([
            'name' => 'Indefinido',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
