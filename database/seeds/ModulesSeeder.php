<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now()->format('Y-m-d H:i:s');

        DB::table('setting_modules')->insert([
            'name' => 'Admin',
            'description' => 'Utilidades ADMIN',
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Satélite',
            'description' => 'Módulo Satélite',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Trabajo',
            'description' => 'Módulo Trabajo',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Alarma',
            'description' => 'Módulo Alarmas',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Noticias',
            'description' => 'Módulo Noticias',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'GBook',
            'description' => 'Módulo GBook',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Recursos Humanos',
            'description' => 'Módulo Recursos Humanos',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Audiovisuales',
            'description' => 'Módulo Audiovisuales',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Maquillaje',
            'description' => 'Módulo Maquillaje',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Inglés',
            'description' => 'Módulo Inglés',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Mantenimiento',
            'description' => 'Módulo Mantenimiento',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Usuarios',
            'description' => 'Módulo Usuarios',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Nómina',
            'description' => 'Módulo Nómina',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Asistencia',
            'description' => 'Módulo Asistencia',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Estadísticas',
            'description' => 'Módulo Estadísticas',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Monitoreo',
            'description' => 'Módulo Monitoreo',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Horario',
            'description' => 'Módulo Horario',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Boutique',
            'description' => 'Módulo Boutique',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Tareas',
            'description' => 'Módulo Tareas',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Roles',
            'description' => 'Módulo Roles',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Locaciones',
            'description' => 'Módulo Locaciones',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Páginas',
            'description' => 'Módulo Páginas',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Sub-Estudios',
            'description' => 'Módulo Sub-Estudios',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Control Cuartos',
            'description' => 'Módulo Control Cuartos',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Módulos',
            'description' => 'Se registran los módulos del sistema',
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Cafetería',
            'description' => 'Módulo Cafetería',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Préstamo',
            'description' => 'Permite realizar una solicitud de préstamo a la empresa',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'API',
            'description' => 'Api Pagos',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Satélite Sub-Estudio',
            'description' => 'Opción únicamente para los sub-estudios. Es igual que satélite de GB',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Capacitaciones',
            'description' => 'Módulo para gestionar capacitaciones, tutoriales o videos explicativos',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Soporte',
            'description' => 'Módulo para gestionar tickets de soporte enviados por correo',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Contacto',
            'description' => 'Esta opción es para guardar los emails que antes se hacía en admin panel de grupo-bedoya',
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Plantillas',
            'description' => 'Módulo para la gestión de las plantillas de correo para las cuentas satélites',
            'is_admin' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Configuración',
            'description' => 'Este módulo permite a los diferentes estudios activar o desactivar acciones dentro de la plataforma GB',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Wiki',
            'description' => 'Módulo para el registro de blogs informativos',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Contratos',
            'description' => 'Módulo para la gestion del exportar contratos para los usuarios',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('setting_modules')->insert([
            'name' => 'Psicologia',
            'description' => 'Módulo para psicologia',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
