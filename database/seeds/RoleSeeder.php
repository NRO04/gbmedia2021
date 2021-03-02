<?php

use Illuminate\Database\Seeder;
use App\Models\Settings\SettingRole;
use Carbon\Carbon;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $position = 1;

        $roles = [
            'Gerente',
            'Asistente Administrativo',
            'Administrador/a',
            'Soporte',
            'Supervisor/a Monitores',
            'Monitor/a',
            'Recursos Humanos',
            'Entrenador/a de Modelos',
            'Fotógrafo/a',
            'Videógrafo/a',
            'Programador/a',
            'Desarrollador/a Web',
            'Técnico de Sistemas',
            'Modelo',
            'Cafetería y Aseo',
            'Mantenimiento y Construcción',
            'Maquillador/a',
            'Profesor/a Inglés',
            'Mensajero/a',
            'Publicista',
            'Auxiliar Boutique',
            'Auxiliar Contable',
            'Auxiliar Soporte Modelos',
            'Monitoreo Satélites',
            'Coordinador/a Audiovisuales',
            'Mayordomo',
            'Niñero/a',
            'Influencer',
            'Servicios Generales',
            'Asesor/a Comercial',
            'Asistencia Gerencia',
            'Supervisor/a Reportes Monitoreo',
            'Auxiliar Administrativo',
            'Asistente Contable',
            'Recursos Humanos Operativo',
            'Psicólogo/a',
            'Tesoreso/a',
            'Productor',
            'Director Financiero',
            'Auxiliar Nómina',
        ];

        foreach ($roles as $role) {
            $role = Role::firstOrCreate([
                'name' => $role,
                'position' => $position++,
            ]);

            if (
                $role->name == 'Gerente' ||
                $role->name == 'Programador/a'
            ) {
                $role->givePermissionTo(Permission::all());
            }
        }


        /*DB::table('setting_roles')->insert([
            'name' => 'Gerente',
            'position' => 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Asistente Administrativo',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Administrador',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Soporte',
            'alternative_name' => 'Secretario/a Administrativo/a',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Supervisor Monitores',
            'alternative_name' => 'Coordinador Asesores y/o Soporte de Transmision',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Monitor',
            'alternative_name' => 'Asesor y/o Soporte de Transmision',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Recursos Humanos',
            'alternative_name' => 'Auxiliar de Recursos Humanos y Seguridad Social',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Entrenador de Modelos',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Fotógrafo',
            'alternative_name' => 'Fotografo o Auxiliar de Audiovisuales',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Videógrafo',
            'alternative_name' => 'Fotografo, Videografo o Auxiliar de Audiovisuales',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Programador',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Desarrollador Web',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Técnico de Sistemas',
            'alternative_name' => 'Soporte Tecnico en Sistemas',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Modelo',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Cafetería y Aseo',
            'alternative_name' => 'Auxiliar de Cafeteria y Oficios varios',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Mantenimiento y Construcción',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Maquilladora',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Profesor Inglés',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Mensajero',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Publicista',
            'position' => $position + 1,
            'alternative_name' => 'Publicista y/o Comunity Manager',
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Auxiliar Boutique',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Auxiliar Contable',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Auxiliar Soporte Modelos',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Monitoreo Satélites',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Coordinador Audiovisuales',
            'alternative_name' => 'Coordinador Fotógrafo, Videógrafo o Auxiliar Audiovisuales',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Mayordomo',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Niñera',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Influencer',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Servicios Generales',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Asesor Comercial',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Asistencia Gerencia',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Supervisor Reportes Monitoreo',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Auxiliar Administrativo',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Asistente Contable',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Recursos Humanos Operativo',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $position = SettingRole::max('position');

        DB::table('setting_roles')->insert([
            'name' => 'Psicólogo',
            'position' => $position + 1,
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);*/
    }
}
