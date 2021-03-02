<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Generator as Faker;

class RoomsControlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker $faker)
    {
        for ($i = 0; $i < 50; $i++) {
            DB::table('rooms_controls')->insert([
                'setting_location_id' => rand(1, 3),
                'room_number' => rand(1, 8),
                'status' => rand(1, 2),
                'date' => $faker->dateTimeBetween($startDate = '-1 week', $endDate = 'now'),
                'model_id' => rand(1, 100),
                'monitor_id' => rand(1, 200),
                'observations' => '[{"item": "decoracion_portaretrato", "observation": ""}, {"item": "decoracion_cuadro_2", "observation": ""}, {"item": "control", "observation": ""}, {"item": "microfono", "observation": ""}, {"item": "sombrilla_con_tripode", "observation": ""}, {"item": "2_camaras_logitech", "observation": ""}, {"item": "teclado_inalambrico", "observation": ""}, {"item": "cabeza_tripode", "observation": ""}, {"item": "2_luces_led", "observation": ""}, {"item": "decoracion_lampara", "observation": ""}, {"item": "7_velas_decorativas", "observation": ""}, {"item": "televisor", "observation": ""}, {"item": "decoracion_cuadro", "observation": ""}, {"item": "tripode", "observation": ""}, {"item": "sillon_en_l_con_5_cojines", "observation": ""}, {"item": "mesa", "observation": ""}, {"item": "decoracion_florero_orquidea", "observation": ""}, {"item": "decoracion_bailarina_sentada", "observation": ""}, {"item": "decoracion_bailarina_sentada_2", "observation": ""}, {"item": "2_nocheros_blanco_y_negro", "observation": ""}, {"item": "cama", "observation": ""}, {"item": "mesa_de_trabajo", "observation": ""}]',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
