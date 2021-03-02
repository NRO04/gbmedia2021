<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SatelliteTemplatePageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        //tipo de plantillas para las cuentas
        $page_type = [
            ['name' =>'Cuenta Creada','created_at' => $now, 'updated_at' => $now],
            ['name' =>'Cuenta Activa','created_at' => $now, 'updated_at' => $now],
            ['name' =>'Cuenta Cerrada','created_at' => $now, 'updated_at' => $now],
            ['name' =>'Ya tiene cuenta','created_at' => $now, 'updated_at' => $now],
            ['name' =>'Docs Pendientes','created_at' => $now, 'updated_at' => $now],
            ['name' =>'Docs no recibidos','created_at' => $now, 'updated_at' => $now],
        ];
        DB::table('satellite_templates_types')->insert($page_type);

        //paginas de las plantillas
        for ($i=1; $i <= 6 ; $i++) 
        { 
            $pages = [
                ['name' =>'Jasmin', 'template_type_id' => $i, 'created_at' => $now, 'updated_at' => $now],
                ['name' =>'Streamate', 'template_type_id' => $i, 'created_at' => $now, 'updated_at' => $now],
                ['name' =>'Chaturbate', 'template_type_id' => $i, 'created_at' => $now, 'updated_at' => $now],
                ['name' =>'Camsoda', 'template_type_id' => $i, 'created_at' => $now, 'updated_at' => $now],
                ['name' =>'Bongacams', 'template_type_id' => $i, 'created_at' => $now, 'updated_at' => $now],
                ['name' =>'Flirt4Free', 'template_type_id' => $i, 'created_at' => $now, 'updated_at' => $now],
                ['name' =>'StripChat', 'template_type_id' => $i, 'created_at' => $now, 'updated_at' => $now],
                ['name' =>'ImLive', 'template_type_id' => $i, 'created_at' => $now, 'updated_at' => $now],
                ['name' =>'XLoveCam', 'template_type_id' => $i, 'created_at' => $now, 'updated_at' => $now],
                ['name' =>'Eurolive', 'template_type_id' => $i, 'created_at' => $now, 'updated_at' => $now],
                ['name' =>'MyKoCam', 'template_type_id' => $i, 'created_at' => $now, 'updated_at' => $now],
                ['name' =>'FireCams', 'template_type_id' => $i, 'created_at' => $now, 'updated_at' => $now],
                ['name' =>'Cams', 'template_type_id' => $i, 'created_at' => $now, 'updated_at' => $now],
                ['name' =>'Cam4', 'template_type_id' => $i, 'created_at' => $now, 'updated_at' => $now],
                ['name' =>'Eplay', 'template_type_id' => $i, 'created_at' => $now, 'updated_at' => $now],
                ['name' =>'Xvrchat', 'template_type_id' => $i, 'created_at' => $now, 'updated_at' => $now],
                ['name' =>'SkyPrivate', 'template_type_id' => $i, 'created_at' => $now, 'updated_at' => $now],
                ['name' =>'OleCams', 'template_type_id' => $i, 'created_at' => $now, 'updated_at' => $now],
            ];

            DB::table('satellite_templates_pages_fields')->insert($pages);
        }

        //estado de las cuentas
        $status = [
            ['name' =>'Pendiente'       , 'color' => 'black', 'background' => 'yellow', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'Activo'          , 'color' => 'white', 'background' => 'green', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'Cerrada'         , 'color' => 'white', 'background' => 'red', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'Ya Tiene Cuenta' , 'color' => 'white', 'background' => 'red', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'Vetado'          , 'color' => 'white', 'background' => 'black', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'Docs Pendientes' , 'color' => 'black', 'background' => 'orange', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'Docs No Recibidos', 'color' => 'black', 'background' => 'orange', 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'Inactiva'        , 'color' => 'black', 'background' => 'orange', 'created_at' => $now, 'updated_at' => $now],
        ];
        DB::table('satellite_accounts_status')->insert($status);

        //paginas de satelite para los archivos subidos
        $payment_pages = [
                [
                    'name' =>'Jasmin', 
                    'type' => 'excel', 
                    'setting_page_id' => 1, 
                    'has_euro' => 0, 
                    'image' => '',
                    'description' => 'Nick y Valor',
                    'cell_nick' => 0,
                    'cell_value' => 1,
                    'created_at' => $now, 
                    'updated_at' => $now],

                [
                    'name' =>'Streamate', 
                    'type' => 'csv', 
                    'setting_page_id' => 2,
                    'has_euro' => 0, 
                    'image' => 'Streamate.png',
                    'description' => '',
                    'cell_nick' => 1,
                    'cell_value' => 7,
                    'created_at' => $now, 
                    'updated_at' => $now],

                [
                    'name' =>'Streamate Studio', 
                    'type' => 'csv', 
                    'setting_page_id' => 2, 
                    'has_euro' => 0, 
                    'image' => 'Streamate_studio.png',
                    'description' => '',
                    'cell_nick' => 1,
                    'cell_value' => 3,
                    'created_at' => $now, 
                    'updated_at' => $now],

                [
                    'name' =>'Streamate Bonos', 
                    'type' => 'excel', 
                    'setting_page_id' => 2, 
                    'has_euro' => 0, 
                    'image' => 'Streamate_bono.png',
                    'description' => '',
                    'cell_nick' => 1,
                    'cell_value' => 4,
                    'created_at' => $now, 
                    'updated_at' => $now],

                [
                    'name' =>'Chaturbate', 
                    'type' => 'excel', 
                    'setting_page_id' => 3, 
                    'has_euro' => 0, 
                    'image' => '',
                    'description' => 'Nick y Valor',
                    'cell_nick' => 0,
                    'cell_value' => 1,
                    'created_at' => $now, 
                    'updated_at' => $now],

                [
                    'name' =>'Camsoda', 
                    'type' => 'excel', 
                    'setting_page_id' => 4, 
                    'has_euro' => 0, 
                    'image' => '',
                    'description' => 'Nick y Valor',
                    'cell_nick' => 0,
                    'cell_value' => 1,
                    'created_at' => $now, 
                    'updated_at' => $now],

                [
                    'name' =>'Bongacams', 
                    'type' => 'excel',
                    'setting_page_id' => 5,
                    'has_euro' => 0,
                    'image' => '',
                    'description' => 'Nick y Valor',
                    'cell_nick' => 0,
                    'cell_value' => 1,
                    'created_at' => $now, 
                    'updated_at' => $now],

                [
                    'name' =>'Flirt4free',
                    'type' => 'excel',
                    'setting_page_id' => 6,
                    'has_euro' => 0,
                    'image' => '',
                    'description' => 'Nick y Valor',
                    'cell_nick' => 0,
                    'cell_value' => 1,
                    'created_at' => $now, 
                    'updated_at' => $now],

                [
                    'name' =>'Stripchat',
                    'type' => 'excel',
                    'setting_page_id' => 7,
                    'has_euro' => 0,
                    'image' => 'Stripchat.png',
                    'description' => '',
                    'cell_nick' => 0,
                    'cell_value' => 2,
                    'created_at' => $now, 
                    'updated_at' => $now],

                [
                    'name' =>'Imlive',
                    'type' => 'excel',
                    'setting_page_id' => 8,
                    'has_euro' => 0,
                    'image' => '',
                    'description' => 'Nick y Valor',
                    'cell_nick' => 0,
                    'cell_value' => 1,
                    'created_at' => $now, 
                    'updated_at' => $now],

                [
                    'name' =>'Xlovecam',
                    'type' => 'text',
                    'setting_page_id' => 9,
                    'has_euro' => 1,
                    'image' => 'Xlovecam.png',
                    'description' => '',
                    'cell_nick' => '',
                    'cell_value' => '',
                    'created_at' => $now, 
                    'updated_at' => $now],

                [
                    'name' =>'XLoveCam Bonos y Seguro',
                    'type' => 'text',
                    'setting_page_id' => 9,
                    'has_euro' => 1,
                    'image' => 'XLoveCam_Bonos_Seguro.png',
                    'description' => '',
                    'cell_nick' => '',
                    'cell_value' => '',
                    'created_at' => $now, 
                    'updated_at' => $now],

                [
                    'name' =>'FireCams',
                    'type' => 'excel',
                    'setting_page_id' => 12,
                    'has_euro' => 0,
                    'image' => '',
                    'description' => 'Nick y Valor',
                    'cell_nick' => 0,
                    'cell_value' => 1,
                    'created_at' => $now, 
                    'updated_at' => $now],

                
                [
                    'name' =>'Cams',
                    'type' => 'excel',
                    'setting_page_id' => 13,
                    'has_euro' => 0,
                    'image' => '',
                    'description' => 'Nick y Valor',
                    'cell_nick' => 0,
                    'cell_value' => 1,
                    'created_at' => $now, 
                    'updated_at' => $now],

                
                [
                    'name' =>'Cam4',
                    'type' => 'excel',
                    'setting_page_id' => 14,
                    'has_euro' => 0,
                    'image' => '',
                    'description' => 'Nick y Valor',
                    'cell_nick' => 0,
                    'cell_value' => 1,
                    'created_at' => $now, 
                    'updated_at' => $now],

                [
                    'name' =>'Xvrchat',
                    'type' => 'excel',
                    'setting_page_id' => 16,
                    'has_euro' => 0,
                    'image' => 'Xvrchat.png',
                    'description' => '',
                    'cell_nick' => 0,
                    'cell_value' => 4,
                    'created_at' => $now, 
                    'updated_at' => $now],

                [
                    'name' =>'Skyprivate',
                    'type' => 'csv',
                    'setting_page_id' => 17,
                    'has_euro' => 0,
                    'image' => 'Skyprivate.png',
                    'description' => '',
                    'cell_nick' => 1,
                    'cell_value' => 3,
                    'created_at' => $now, 
                    'updated_at' => $now],

                [
                    'name' =>'Olecams',
                    'type' => 'excel',
                    'setting_page_id' => 18,
                    'has_euro' => 1,
                    'image' => 'Olecams.png',
                    'description' => '',
                    'cell_nick' => 1,
                    'cell_value' => 13,
                    'created_at' => $now, 
                    'updated_at' => $now],

                
                
            ];

            DB::table('satellite_payment_pages')->insert($payment_pages);
    }
}
