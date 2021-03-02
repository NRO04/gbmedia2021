<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserContractsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        DB::table('contracts')->insert([
            'title' => 'Anexo Cuentas Participación',
            'description' => 'ExportarContrato',
            'url' => 'ExportContrato',
            'image' => 'contract1.png',
            'position' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('contracts')->insert([
            'title' => 'Contrato de Arrendamiento',
            'description' => 'ExportContratoAT',
            'url' => 'ExportContratoAT',
            'image' => 'contract2.png',
            'position' => 2,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('contracts')->insert([
            'title' => 'Marketing & Publicidad',
            'description' => 'ExportContratoMP',
            'url' => 'ExportContratoAT',
            'image' => 'contract3.png',
            'position' => 3,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('contracts')->insert([
            'title' => 'Tratamiento de Datos',
            'description' => 'ExportContratoTD',
            'url' => 'ExportContratoTD',
            'image' => 'contract4.png',
            'position' => 4,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('contracts')->insert([
            'title' => 'Cuentas en Participación',
            'description' => 'ExportContratoCP',
            'url' => 'ExportContratoCP',
            'image' => 'contract5.png',
            'position' => 5,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('contracts')->insert([
            'title' => 'Contrato Mandato',
            'description' => 'ExportContratoCM',
            'url' => 'ExportContratoCM',
            'image' => 'contract6.png',
            'position' => 6,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('contracts')->insert([
            'title' => 'Cesión de Derechos',
            'description' => 'ExportContratoCD',
            'url' => 'ExportContratoCD',
            'image' => 'contract7.png',
            'position' => 7,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('contracts')->insert([
            'title' => 'Acuerdo de Confidencialidad',
            'description' => 'ExportContratoAC',
            'url' => 'ExportContratoAC',
            'image' => 'contract8.png',
            'position' => 8,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('contracts')->insert([
            'title' => 'Pagaré',
            'description' => 'Pagare',
            'url' => 'Pagare',
            'image' => 'contract9.png',
            'position' => 9,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('contracts')->insert([
            'title' => 'Contrato Término Indefinido',
            'description' => 'ExportContratoTI',
            'url' => 'ExportContratoTI',
            'image' => 'contract10.png',
            'position' => 10,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('contracts')->insert([
            'title' => 'Contrato de Prestación de Servicios',
            'description' => 'ExportContratoPS',
            'url' => 'ExportContratoPS',
            'image' => 'contract11.png',
            'position' => 11,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('contracts_module_info')->insert([
            'title' => 'Información del Modulo Contratos',
            'description' => '<p style="text-align: center;"><span style="color: #e03e2d;">Este es un m&oacute;dulo pago extra con +10 contratos elaborados por expertos abogados y contadores de la industria, perfeccionados durante a&ntilde;os de experiencia, consulte con nosotros acerca del costo.</span></p>
                                <p style="text-align: center;"><span style="color: #ffffff;">Al activar este modulo, simplemente por medio de un clic podra exportar cada respectivo contrato para cada colaborador, con la informacion de cada usuario ya contenido en ellos y listos para firmar. No tendra que gestionar/llenar o cambiar informacion en ellos, todo lo realizara el sistema.</span></p>
                                <p><span style="color: #ffffff;">Dentro de los contratos encontrara:</span></p>
                                <hr />
                                <ul>
                                <li><strong><span style="color: #ffffff;">Modelos</span></strong>
                                <ul>
                                <li><span style="color: #ffffff;">Declaracion juramentada Entrevista Inicial</span></li>
                                <li><span style="color: #ffffff;">Contrato Cuentas en Participacion</span></li>
                                <li><span style="color: #ffffff;">Anexo Contrato cuentas en Participacion</span></li>
                                <li><span style="color: #ffffff;">Contrato de arrendo temporal de Bienes e Immueble</span></li>
                                <li><span style="color: #ffffff;">Pagare en Blanco y Carta de Autorizacion</span></li>
                                <li><span style="color: #ffffff;">Contrato de Marketing Personal y Publicidad</span></li>
                                <li><span style="color: #ffffff;">Tratamiento de Datos Personales</span></li>
                                <li><span style="color: #ffffff;">Contrato de Mandato</span></li>
                                <li><span style="color: #ffffff;">Cesion de derechos de Imagen</span></li>
                                <li><span style="color: #ffffff;">Acuerdo de Confidencialidad</span></li>
                                </ul>
                                </li>
                                </ul>
                                <ul>
                                <li><strong><span style="color: #ffffff;">Colaboradores o Empleados</span></strong>
                                <ul>
                                <li><span style="color: #ffffff;">Tratamiento de Habeas Data</span></li>
                                <li><span style="color: #ffffff;">Contrato de Prestacion de Servicios</span></li>
                                <li><span style="color: #ffffff;">Contrato a termino Indefinido</span></li>
                                <li><span style="color: #ffffff;">Acuerdo de Confidencialidad</span></li>
                                </ul>
                                </li>
                                </ul>',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('tenant_has_contracts')->insert([
            'tenant_id' => 1,
            'active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
