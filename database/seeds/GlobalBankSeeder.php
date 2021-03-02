<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class GlobalBankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        $banks = [
            ['name' =>'BANCO AGRARIO', 'code' => 1041 ,'created_at' => $now, 'updated_at' => $now],
            ['name' =>'BANCO AV VILLAS', 'code' => 1052, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'BANCO BBVA', 'code' => 1013, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'BANCO CAJA SOCIAL', 'code' => 1032, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'BANCO COLPATRIA', 'code' => 1019, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'BANCO COMPARTIR S.A.', 'code' => 1067, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'BANCO COOPERATIVO COOPCENTRAL', 'code' => 1066, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'BANCO DAVIVIENDA', 'code' => 1051, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'BANCO DE BOGOTA', 'code' => 1001, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'BANCO DE LAS MICROFINANZAS - BANCAMIA S.A.', 'code' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'BANCO DE OCCIDENTE', 'code' => 1023, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'BANCO FALABELLA S.A.', 'code' => 1062, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'BANCO FINANDINA S.A.', 'code' => 1063, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'BANCO GNB SUDAMERIS', 'code' => 1012, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'BANCO MULTIBANKS S.A.', 'code' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'BANCO PICHINCHA', 'code' => 1060, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'BANCO POPULAR', 'code' => 1002, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'BANCO PROCREDIT COLOMBIA', 'code' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'BANCO SANTANDER DE NEGOCIOS COLOMBIA S.A.', 'code' => 1065, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'BANCO W S.A.', 'code' => 1053, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'BANCOLOMBIA', 'code' => 1007, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'BANCOOMEVA', 'code' => 1061, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'BANCÓLDEX S.A.', 'code' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'CITIBANK', 'code' => 1009, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'COLTEFINANCIERA S.A', 'code' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'CONFIAR', 'code' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'COOPERATIVA FINANCIERA DE ANTIOQUIA', 'code' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'COTRAFA COOPERATIVA FINANCIERA', 'code' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'DAVIPLATA', 'code' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'FIDUCIARIA BANCOLOMBIA', 'code' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'FIDUCIARIA SKANDIA', 'code' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'FINANCIERA JURISCOOP S.A. COMPAÑIA DE FINANCIAMIENTOS', 'code' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'FONDO DE INVERSIÓN COLECTIVA', 'code' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'ITAÚ', 'code' => 1006, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'ITAÚ antes CorpbancaS', 'code' => 1006, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'NEQUI', 'code' => 1507, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'SCOTIABANK COLPATRIA S.A', 'code' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['name' =>'VALORES BANCOLOMBIA', 'code' => 0, 'created_at' => $now, 'updated_at' => $now],
        ];

        DB::table('global_banks')->insert($banks);
    }
}
