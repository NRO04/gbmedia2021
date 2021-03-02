<?php

use Illuminate\Database\Seeder;
use App\Models\Settings\SettingTask;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $position = SettingTask::max('position');

        DB::table('setting_tasks')->insert([
            'name' => 'ninguna',
            'description' => '',
            'see' => 0,
            'edit' => 0,
            'delete' => 0,
            'position' => $position + 1,
            'module_id' => 1,
        ]);


        DB::table('setting_tasks')->insert([
            'name' => 'Registrar Usuario',
            'description' => 'Crear o registrar un nuevo usuario en la plataforma',
            'see' => 1,
            'edit' => 1,
            'delete' => 1,
            'position' => $position + 1,
            'module_id' => 12,
        ]);

        $position = SettingTask::max('position');

        DB::table('setting_tasks')->insert([
            'name' => 'Listado Usuarios',
            'description' => 'Permiso para ver, editar y/o eliminar usuarios',
            'see' => 1,
            'edit' => 1,
            'delete' => 1,
            'position' => $position + 1,
            'module_id' => 12,
        ]);

        $position = SettingTask::max('position');

        DB::table('setting_tasks')->insert([
            'name' => 'Crear Rol',
            'description' => 'Permiso para crear un nuevo rol',
            'see' => 0,
            'edit' => 1,
            'delete' => 0,
            'position' => $position + 1,
            'module_id' => 20,
        ]);

        $position = SettingTask::max('position');

        DB::table('setting_tasks')->insert([
            'name' => 'Listado de Roles',
            'description' => 'Permiso para ver, editar y/o eliminar roles',
            'see' => 1,
            'edit' => 1,
            'delete' => 1,
            'position' => $position + 1,
            'module_id' => 20,
        ]);

        $position = SettingTask::max('position');

        DB::table('setting_tasks')->insert([
            'name' => 'Crear Tarea',
            'description' => 'Permiso para crear una nueva tarea',
            'see' => 0,
            'edit' => 1,
            'delete' => 0,
            'position' => $position + 1,
            'module_id' => 19,
        ]);

        $position = SettingTask::max('position');

        DB::table('setting_tasks')->insert([
            'name' => 'Listado de Tareas',
            'description' => 'Permiso para ver, editar y/o eliminar tareas',
            'see' => 1,
            'edit' => 1,
            'delete' => 1,
            'position' => $position + 1,
            'module_id' => 19,
        ]);

        $position = SettingTask::max('position');

        DB::table('setting_tasks')->insert([
            'name' => 'Usuarios Inactivos',
            'description' => 'Permiso para gestionar los usuarios inactivados en la plataforma',
            'see' => 1,
            'edit' => 1,
            'delete' => 1,
            'position' => $position + 1,
            'module_id' => 12,
        ]);

        $position = SettingTask::max('position');

        DB::table('setting_tasks')->insert([
            'name' => 'Crear Locación',
            'description' => 'Permiso para crear una nueva locación',
            'see' => 0,
            'edit' => 1,
            'delete' => 0,
            'position' => $position + 1,
            'module_id' => 21,
        ]);

        $position = SettingTask::max('position');

        DB::table('setting_tasks')->insert([
            'name' => 'Listado Locaciones',
            'description' => 'Permiso para ver, editar y/o eliminar tareas',
            'see' => 1,
            'edit' => 1,
            'delete' => 1,
            'position' => $position + 1,
            'module_id' => 21,
        ]);

        $position = SettingTask::max('position');

        DB::table('setting_tasks')->insert([
            'name' => 'Alarma Expiración de Documentos',
            'description' => 'Permiso para ver la alarma generada por expiración de documentos',
            'see' => 1,
            'edit' => 0,
            'delete' => 0,
            'position' => $position + 1,
            'module_id' => 4,
        ]);

        $position = SettingTask::max('position');

        DB::table('setting_tasks')->insert([
            'name' => 'Crear Página',
            'description' => 'Permiso para crear una nueva página',
            'see' => 0,
            'edit' => 1,
            'delete' => 0,
            'position' => $position + 1,
            'module_id' => 22,
        ]);

        $position = SettingTask::max('position');

        DB::table('setting_tasks')->insert([
            'name' => 'Listado de Páginas',
            'description' => 'Permiso para ver, editar y/o eliminar páginas',
            'see' => 1,
            'edit' => 1,
            'delete' => 1,
            'position' => $position + 1,
            'module_id' => 22,
        ]);

        $position = SettingTask::max('position');

        DB::table('setting_tasks')->insert([
            'name' => 'Listado de Modelos',
            'description' => 'Permiso para ver el listado de modelos activas',
            'see' => 1,
            'edit' => 0,
            'delete' => 0,
            'position' => $position + 1,
            'module_id' => 12,
        ]);

        $position = SettingTask::max('position');

        DB::table('setting_tasks')->insert([
            'name' => 'Crear Acceso de Modelos a Páginas',
            'description' => 'Crear acceso de modelos a cuentas de las páginas creadas',
            'see' => 0,
            'edit' => 1,
            'delete' => 0,
            'position' => $position + 1,
            'module_id' => 22,
        ]);

        $position = SettingTask::max('position');

        DB::table('setting_tasks')->insert([
            'name' => 'Accesos Modelo a Páginas',
            'description' => 'Editar y/o eliminar los accesos de las modelos a las paginas. Ej.: cuando se cambió contraseña o cuando algo se registró por error',
            'see' => 0,
            'edit' => 1,
            'delete' => 1,
            'position' => $position + 1,
            'module_id' => 22,
        ]);

        $position = SettingTask::max('position');

        DB::table('setting_tasks')->insert([
            'name' => 'Crear Horario',
            'description' => 'Permiso para crear un nuevo turno en los horarios. Ej: mañana, tarde o noche y el número de equipos disponibles',
            'see' => 0,
            'edit' => 1,
            'delete' => 0,
            'position' => $position + 1,
            'module_id' => 17,
        ]);

        $position = SettingTask::max('position');

        DB::table('setting_tasks')->insert([
            'name' => 'Listado de Horarios',
            'description' => 'Permiso para ver el listado de horarios creados',
            'see' => 1,
            'edit' => 1,
            'delete' => 1,
            'position' => $position + 1,
            'module_id' => 17,
        ]);

        $position = SettingTask::max('position');

        DB::table('setting_tasks')->insert([
            'name' => 'Estadísticas',
            'description' => 'Permisos para ver y/o editar las estadísticas de ventas de las modelos en las páginas',
            'see' => 1,
            'edit' => 1,
            'delete' => 0,
            'position' => $position + 1,
            'module_id' => 15,
        ]);

        $position = SettingTask::max('position');

        DB::table('setting_tasks')->insert([
            'name' => 'Usuario Información de Contacto',
            'description' => 'Visualización de la información de contacto',
            'see' => 1,
            'edit' => 1,
            'delete' => 0,
            'position' => $position + 1,
            'module_id' => 12,
        ]);
    }
}
