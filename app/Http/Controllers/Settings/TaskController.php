<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Settings\SettingRole;
use App\Models\Settings\SettingRoleHasTasks;
use App\Models\Settings\SettingTask;
use App\Models\Settings\SettingModule;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function list($module_id = null)
    {
        $module_name = '';

        if(!is_null($module_id)) {
            $module = SettingModule::find($module_id);
            $module_name = $module->name;
        }

        $roles = SettingRole::where('id', '!=', 1)->orderBy('name', 'asc')->get();;

        return view('adminModules.setting.task.list',
            ['roles' => $roles, 'module_id' => $module_id, 'module_name' => $module_name]
        );
    }

    public function getTasks(SettingTask $tasks, Request $request, $module_id = null)
    {
        $data = [];

        if(is_null($module_id)) {
            $tasks = $tasks->get();
        } else {
            $tasks = $tasks->get()->where('module_id', $module_id);
        }

        $edit_permission = true;
        $delete_permission = true;
        $position_permission = true;

        foreach ($tasks as $task)
        {
            $btn_description = "<button type='button' class='btn btn-sm btn-primary' data-toggle='tooltip' data-placement='top' title='$task->description'><i class='fa fa-question-circle'></i></button>";
            $btn_edit = ($edit_permission) ? "<button type='button' class='btn btn-sm btn-warning' onclick='Edit($task->id)'><i class='fa fa-edit'></i></button>" : "";
            $btn_delete = ($delete_permission) ? "<button type='button' class='btn btn-sm btn-danger' onclick='Delete(".$task->id.")'><i class='fa fa-trash'></i></button>" : "";
            $btn_positions = ($position_permission) ? "<button class='btn btn-sm btn-primary' title='Subir posición'><i class='fa fa-arrow-up'></i></button> <button class='btn btn-sm btn-primary' title='Bajar posición'><i class='fa fa-arrow-down'></i></button>" : "";

            $data[] = [
                "name" => $task->name,
                "description" => $task->description,
                "btn_edit" => $btn_edit,
                "position" => $btn_positions
            ];
        }

        return datatables($data)
            ->rawColumns(['description', 'btn_edit', 'btn_delete', 'position'])
            ->toJson();
    }

    public function saveTask(Request $request)
    {

        $this->validate($request,
            [
                'name' => 'required|unique:setting_tasks,name|max:128',
                'description' => 'required'
            ],
            [
                'name.required' => 'Este campo es obligatorio',
                'name.unique' => 'El nombre de la tarea ya existe',
                'description.required' => 'Debe ingresar la descripción de la tarea',
            ]
        );

        $task = new SettingTask();
        $task->name = $request->name;
        $task->description = $request->description;
        $task->module_id = $request->module_id;
        $task->see = isset($request->checkbox_see) ? 1 : 0;
        $task->edit = isset($request->checkbox_edit) ? 1 : 0;
        $task->delete = isset($request->checkbox_delete) ? 1 : 0;
        $task->position = SettingTask::max('position') + 1;
        $success = $task->save();

        return response()->json(['success' => $success]);
    }

    public function getTask(Request $request)
    {
        $task = new SettingTask();
        $id = $request->input('task_id');

        $data = $task->find($id);
        $permissions = $data->role_has_task;

        $roles = [];

        foreach ($permissions AS $role) {
            $roles[] = [
                'id' => $role->setting_role_id,
                'see' => $role->see,
                'edit' => $role->edit,
                'delete' => $role->delete,
            ];
        }

        $data = [
            'task' => $data,
            'roles' => $roles,
        ];

        return response()->json($data);
    }

    public function editTask(Request $request)
    {
        $this->validate($request,
            [
                'edit_description' => 'required'
            ],
            [
                'edit_description.required' => 'Debe ingresar la descripción de la tarea',
            ]
        );

        $task_id = $request->input('task_id');

        $task = SettingTask::find($task_id);
        $task->description = $request->input('edit_description');
        $success = $task->save();

        if($success) {
            $roles = $request->input('roles');

            if($roles) {
                $this->resetPermissionsToTask($task_id); // Reset task permissions

                foreach($roles AS $role_id => $role) {
                    $see = isset($role['see']) ? 1 : null;
                    $edit = isset($role['edit']) ? 1 : null;
                    $delete = isset($role['delete']) ? 1 : null;

                    $permissions = new SettingRoleHasTasks();

                    $permissions->setting_task_id = $task_id;
                    $permissions->setting_role_id = $role_id;
                    $permissions->see = $see;
                    $permissions->edit = $edit;
                    $permissions->delete = $delete;
                    $permissions->save();
                }
            }
        }

        return response()->json(['success' => $success]);
    }

    public function resetPermissionsToTask($task_id)
    {
        SettingRoleHasTasks::where('setting_task_id', $task_id)->delete();
    }
}
