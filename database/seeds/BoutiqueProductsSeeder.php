<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Generator as Faker;

class BoutiqueProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker $faker)
    {
        DB::table('boutique_blocked_value')->insert([
            'value' => 70000,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Categories
        $categories = [
            'Babuchas',
            'Plugs',
            'Dildos',
            'Vibradores',
            'Bodys',
            'Camisetas',
            'Gorros',
            'Medias De Algodon',
            'Medias Veladas',
            'Kimonos',
            'BabyDolls',
            'Pijamas',
            'Conjuntos',
            'Trajes y Disfraces',
            'Panties',
            'Lubricantes',
            'Faldas',
            'Shorts',
            'Accesorios',
            'Fetiches',
            'Brasier',
            'Uso Personal',
            'Media Malla',
            'Cosmeticos',
        ];

        foreach ($categories AS $category) {
            DB::table('boutique_categories')->insert([
                'name' => $category,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        // Products
        DB::table('boutique_products')->insert([
            'name' => 'Media Algodon Perrito',
            'boutique_category_id' => 8,
            'image' => '5f64b83eda07e_1600436286.jpg',
            'unit_price' => 50000,
            'wholesaler_price' => 45000,
            'nationality' => 'Internacional',
            'active' => 1,
            'barcode' => $faker->ean13,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('boutique_products')->insert([
            'name' => 'Media Algodon Negro/Azul',
            'boutique_category_id' => 8,
            'image' => '5f64b83eda07e_1600436286.jpg',
            'unit_price' => 50000,
            'wholesaler_price' => 45000,
            'nationality' => 'Internacional',
            'active' => 1,
            'barcode' => $faker->ean13,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('boutique_products')->insert([
            'name' => 'Media Algodon Negro/Fucsia',
            'boutique_category_id' => 8,
            'image' => '5f64b83eda07e_1600436286.jpg',
            'unit_price' => 50000,
            'wholesaler_price' => 42000,
            'nationality' => 'Internacional',
            'active' => 1,
            'barcode' => $faker->ean13,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('boutique_products')->insert([
            'name' => 'BabyDoll Estructurado Beige',
            'boutique_category_id' => 11,
            'image' => '5f64b83eda07e_1600436286.jpg',
            'unit_price' => 65000,
            'wholesaler_price' => 60250,
            'nationality' => 'Internacional',
            'active' => 1,
            'barcode' => $faker->ean13,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('boutique_products')->insert([
            'name' => 'BabyDoll Estructurado Fucsia',
            'boutique_category_id' => 11,
            'image' => '5f64b83eda07e_1600436286.jpg',
            'unit_price' => 65000,
            'wholesaler_price' => 55000,
            'nationality' => 'Internacional',
            'active' => 1,
            'barcode' => $faker->ean13,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('boutique_products')->insert([
            'name' => 'BabyDoll Estructurado Azul',
            'boutique_category_id' => 11,
            'image' => '5f64b83eda07e_1600436286.jpg',
            'unit_price' => 65000,
            'wholesaler_price' => 60000,
            'nationality' => 'Internacional',
            'active' => 1,
            'barcode' => $faker->ean13,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('boutique_products')->insert([
            'name' => 'Kimono + Conjunto Negro',
            'boutique_category_id' => 10,
            'image' => '5f64b83eda07e_1600436286.jpg',
            'unit_price' => 50000,
            'wholesaler_price' => 47250,
            'nationality' => 'Internacional',
            'active' => 1,
            'barcode' => $faker->ean13,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('boutique_products')->insert([
            'name' => 'Kimono + Conjunto Blanco',
            'boutique_category_id' => 10,
            'image' => '5f64b83eda07e_1600436286.jpg',
            'unit_price' => 60000,
            'wholesaler_price' => 58200,
            'nationality' => 'Internacional',
            'active' => 1,
            'barcode' => $faker->ean13,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('boutique_products')->insert([
            'name' => 'Kimono Verde Menta',
            'boutique_category_id' => 10,
            'image' => '5f64b83eda07e_1600436286.jpg',
            'unit_price' => 75000,
            'wholesaler_price' => 72000,
            'nationality' => 'Internacional',
            'active' => 1,
            'barcode' => $faker->ean13,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('boutique_products')->insert([
            'name' => 'BabyDoll Satin Verde',
            'boutique_category_id' => 11,
            'image' => '5f64b83eda07e_1600436286.jpg',
            'unit_price' => 50000,
            'wholesaler_price' => 42600,
            'nationality' => 'Internacional',
            'active' => 1,
            'barcode' => $faker->ean13,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('boutique_products')->insert([
            'name' => 'BabyDoll Satin Azul Navy',
            'boutique_category_id' => 11,
            'image' => '5f64b83eda07e_1600436286.jpg',
            'unit_price' => 50000,
            'wholesaler_price' => 41850,
            'nationality' => 'Internacional',
            'active' => 1,
            'barcode' => $faker->ean13,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Inventories
        $locations = \App\Models\Settings\SettingLocation::where('id', '!=', 1)->get();

        for ($i = 1; $i <= 11; $i++) {
            foreach ($locations AS $location) {
                DB::table('boutique_inventories')->insert([
                    'boutique_product_id' => $i,
                    'setting_location_id' => $location->id,
                    'quantity' => rand(1, 15),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }
}
