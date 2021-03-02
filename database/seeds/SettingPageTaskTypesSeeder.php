<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class SettingPageTaskTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        $types = [

            ['name' => 'Boton', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Scroll opcion', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Scroll opcion varios', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Valor en aumento', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Valor en aumento y descenso', 'created_at' => $now, 'updated_at' => $now],

        ];

        DB::table('setting_page_task_types')->insert($types);
    }
}
