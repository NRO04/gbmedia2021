<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CafeteriaBreakfastTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            ['Cacerola', 700, 1],
            ['Huevos cocidos', 700, 1],
            ['Pericos', 1000, 1],
            ['Omelet', 1750, 1],
            ['Huevos revueltos', 700, 1],
            ['Mango', 3000, 2],
            ['Piña', 2000, 2],
            ['Fresa', 3500, 2],
            ['Papaya', 2000, 2],
            ['Ensalada de frutas', 4000, 2],
            ['Arepa sola', 1500, 3],
            ['Arepa Tela', 1000, 3],
            ['Arepa con queso cuajada', 2300, 3],
            ['Arepa con queso crema', 2300, 3],
            ['Arepa con jamón y queso', 2800, 3],
            ['Pandebono', 1300, 3],
            ['Pan queso', 1500, 3],
            ['Almojábana', 1300, 3],
            ['Bunnuelo', 1300, 3],
            ['Pan tajado (Panaderia)', 500, 3],
            ['Pan tostado (Sandwich)', 300, 3],
            ['Pan seda (Pan teta)', 1400, 3],
            ['Café negro', 500, 4],
            ['Café con leche', 1000, 4],
            ['Aguapanela', 1000, 4],
            ['Milo', 2500, 4],
            ['Chocolate', 2000, 4],
            ['Vaso de leche', 1500, 4],
            ['Jugo de naranja puro', 2000, 4],
            ['Jugo de mora espeso', 2000, 4],
            ['Jugo de fresa espeso', 2500, 4],
            ['Jugo de mango espeso', 2500, 4],
            ['Batido tropical', 3000, 4],
            ['Tizana', 1000, 4],
            ['Maicitos', 1500, 5],
            ['Maduro', 500, 5],
            ['Leche condesada', 1000, 5],
            ['Crema de leche', 1000, 5],
            ['Queso cuajada', 1200, 5],
            ['Queso tajado', 600, 5],
            ['Salchicha ranchera', 1100, 5],
            ['Jamón', 600, 5],
            ['Tajadas', 500, 5],
            ['Papas a la francesa', 3500, 5],
        ];

        foreach ($types AS $type) {
            DB::table('cafeteria_breakfast_types')->insert([
                'name' => $type[0],
                'price' => $type[1],
                'cafeteria_breakfast_category_id' => $type[2],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
