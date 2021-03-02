<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = \Carbon\Carbon::now();

        DB::table('tenants')->insert([
            'id' => 1,
            'created_at' => $now,
            'updated_at' => $now,
            'data' => json_encode(
                [
                    'tenancy_db_name' => 'laravel',
                    'studio_name' => 'Grupo Bedoya',
                    'studio_slug' => 'GB',
                    'studio_url' => 'gblaravel.test',
                    'studio_logo' => 'logo.png',
                    'is_active' => 1,
                    'studio_owner_id' => null,
                    'rooms_control_code' => '1001',
                    'unique_code' => '2dIDnwmrgoZxNlm',
                    'support_db_name' => 'gbmediag_support',
                    'support_db_user' => 'gbmediag_admin',
                    'support_db_passcode' => '4JGPsE3ehX-v',
                    'support_url' => 'https://soporte.gbmediagroup.com',
                    'should_share' => 0,
                    'owner_id' => 0,
                ]
            )
        ]);

        DB::table('tenants')->insert([
            'id' => 7,
            'created_at' => $now,
            'updated_at' => $now,
            'data' => json_encode(
                [
                    'tenancy_db_name' => 'laravel',
                    'studio_name' => 'Grupo RO',
                    'studio_slug' => 'GB',
                    'studio_url' => 'gbmedia2021.test',
                    'studio_logo' => 'logo.png',
                    'is_active' => 1,
                    'studio_owner_id' => null,
                    'rooms_control_code' => '1001',
                    'unique_code' => '2dIDnwmrgoZxNlm',
                    'support_db_name' => 'gbmediag_support',
                    'support_db_user' => 'gbmediag_admin',
                    'support_db_passcode' => '4JGPsE3ehX-v',
                    'support_url' => 'https://soporte.gbmediagroup.com',
                    'should_share' => 0,
                    'owner_id' => 0,
                ]
            )
        ]);

        DB::table('domains')->insert([
            'domain' => 'gblaravel.test',
            'tenant_id' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('domains')->insert([
            'domain' => 'gbmedia2021.test',
            'tenant_id' => 7,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('tenant_has_tenants')->insert([
            'tenant_id' => 1,
            'has_tenant_id' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('tenants')->insert([
            'id' => 2,
            'created_at' => $now,
            'updated_at' => $now,
            'data' => json_encode(
                [
                    'tenancy_db_name' => 'trend_studio',
                    'studio_name' => 'Trend',
                    'studio_slug' => 'trend',
                    'studio_url' => 'trend.gblaravel.test',
                    'studio_logo' => 'logo.png',
                    'is_active' => 1,
                    'studio_owner_id' => null,
                    'rooms_control_code' => '1002',
                    'unique_code' => '',
                    'support_db_name' => '',
                    'support_db_user' => '',
                    'support_db_passcode' => '',
                    'support_url' => '',
                    'should_share' => 0,
                    'owner_id' => 0,
                ]
            )
        ]);

        DB::table('domains')->insert([
            'domain' => 'trend.gblaravel.test',
            'tenant_id' => 2,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('tenant_has_tenants')->insert([
            'tenant_id' => 2,
            'has_tenant_id' => 2,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('tenants')->insert([
            'id' => 3,
            'created_at' => $now,
            'updated_at' => $now,
            'data' => json_encode(
                [
                    'tenancy_db_name' => 'img_studio',
                    'studio_name' => 'IMG',
                    'studio_slug' => 'img',
                    'studio_url' => 'img.gblaravel.test',
                    'studio_logo' => 'logo.png',
                    'is_active' => 1,
                    'studio_owner_id' => null,
                    'rooms_control_code' => '1003',
                    'unique_code' => '',
                    'support_db_name' => '',
                    'support_db_user' => '',
                    'support_db_passcode' => '',
                    'support_url' => '',
                    'should_share' => 0,
                    'owner_id' => 0,
                ]
            )
        ]);

        DB::table('domains')->insert([
            'domain' => 'img.gblaravel.test',
            'tenant_id' => 3,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('tenant_has_tenants')->insert([
            'tenant_id' => 3,
            'has_tenant_id' => 3,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('tenants')->insert([
            'id' => 4,
            'created_at' => $now,
            'updated_at' => $now,
            'data' => json_encode(
                [
                    'tenancy_db_name' => 'soul_studio',
                    'studio_name' => 'Soul',
                    'studio_slug' => 'soul',
                    'studio_url' => 'soul.gblaravel.test',
                    'studio_logo' => 'logo.png',
                    'is_active' => 1,
                    'studio_owner_id' => null,
                    'rooms_control_code' => '1004',
                    'unique_code' => '',
                    'support_db_name' => '',
                    'support_db_user' => '',
                    'support_db_passcode' => '',
                    'support_url' => '',
                    'should_share' => 0,
                    'owner_id' => 0,
                ]
            )
        ]);

        DB::table('domains')->insert([
            'domain' => 'soul.gblaravel.test',
            'tenant_id' => 4,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('tenant_has_tenants')->insert([
            'tenant_id' => 4,
            'has_tenant_id' => 4,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('tenants')->insert([
            'id' => 5,
            'created_at' => $now,
            'updated_at' => $now,
            'data' => json_encode(
                [
                    'tenancy_db_name' => 'lotus_studio',
                    'studio_name' => 'Lotus',
                    'studio_slug' => 'lotus',
                    'studio_url' => 'lotus.gblaravel.test',
                    'studio_logo' => 'logo.png',
                    'is_active' => 1,
                    'studio_owner_id' => null,
                    'rooms_control_code' => '1005',
                    'unique_code' => '',
                    'support_db_name' => '',
                    'support_db_user' => '',
                    'support_db_passcode' => '',
                    'support_url' => '',
                    'should_share' => 0,
                    'owner_id' => 0,
                ]
            )
        ]);

        DB::table('domains')->insert([
            'domain' => 'lotus.gblaravel.test',
            'tenant_id' => 5,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('tenant_has_tenants')->insert([
            'tenant_id' => 5,
            'has_tenant_id' => 5,
            'created_at' => $now,
            'updated_at' => $now,
        ]);


        // Assignments to GB

        /*DB::table('tenant_has_tenants')->insert([
            'tenant_id' => 1,
            'has_tenant_id' => 2,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('tenant_has_tenants')->insert([
            'tenant_id' => 1,
            'has_tenant_id' => 3,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('tenant_has_tenants')->insert([
            'tenant_id' => 1,
            'has_tenant_id' => 4,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('tenant_has_tenants')->insert([
            'tenant_id' => 1,
            'has_tenant_id' => 5,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('user_has_tenants')->insert([
            'from_user_id' => 1,
            'from_tenant_id' => 1,
            'to_user_id' => 1,
            'to_tenant_id' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('user_has_tenants')->insert([
            'from_user_id' => 1,
            'from_tenant_id' => 1,
            'to_user_id' => 5,
            'to_tenant_id' => 2,
            'created_at' => $now,
            'updated_at' => $now,
        ]);*/
    }
}
