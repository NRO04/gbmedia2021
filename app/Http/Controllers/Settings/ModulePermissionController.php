<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Settings\SettingRole;
use App\Models\Settings\SettingRoleHasPermission;
use App\Models\Settings\SettingModulePermission;
use App\Models\Settings\SettingModule;
use App\User;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ModulePermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function list($module_id = null)
    {
        if ($module_id == 1)
        {
            return redirect()->back();
        }
        $module_name = '';

        if(!is_null($module_id)) {
            $module = SettingModule::find($module_id);
            $module_name = $module->name;
        }

        $roles = SettingRole::where('name', '!=', 'sin rol')->orderBy('name', 'asc')->get();

        return view('adminModules.setting.permission.list',
            ['roles' => $roles, 'module_id' => $module_id, 'module_name' => $module_name]
        );
    }

    public function getPermissions(SettingModulePermission $permissions, Request $request, $module_id = null)
    {
        if ($request->ajax()) {
            if(is_null($module_id)) {
                $permissions = $permissions::where('is_parent', true)->get();
            } else {
                $permissions = $permissions::where('is_parent', true)->get()->where('module_id', $module_id);
            }

            $user = Auth::user();

            return DataTables::of($permissions)
                ->addIndexColumn()
                ->addColumn('name', function($row) {
                    return "<div class='text-muted'>$row->name</div>";
                })
                ->addColumn('description', function($row) {
                    return "<button type='button' class='btn btn-sm btn-info' id='btn-description-$row->id' onclick='editDescription($row->id)' data-toggle='tooltip' data-html='true' data-original-title='$row->description'><i class='fa fa-info-circle'></i></button>";
                })
                ->addColumn('roles', function($row) {
                    $roles = $this->getRolesByPermissions($row->name);
                    return "<span class='badge badge-info'  data-placement='top' data-toggle='tooltip' title='" . $roles->implode(', ') ."'>" . $roles->count() . "</span>";
                })
                ->addColumn('actions', function($row) use ($user) {
                    $return = "";

                    if($user->can('assign-permissions-view')) {
                        $return .= "<button type='button' class='btn btn-sm btn-success' onclick='assignPermissions($row->id)'><i class='fa fa-user-tag'></i></button>";
                    }

                    return $return;
                })
                ->rawColumns(['actions', 'description', 'roles', 'name'])
                ->make(true);
        }
    }

    public function savePermission(Request $request)
    {
        $this->validate($request,
            [
                'display_name' => 'required|unique:setting_module_permissions,name|max:128',
                'slug' => 'required|unique:setting_module_permissions,name|max:128',
                'description' => 'required'
            ],
            [
                'display_name.required' => 'Este campo es obligatorio',
                'display_name.unique' => 'El nombre de la tarea ya existe',
                'slug.required' => 'Este campo es obligatorio',
                'slug.unique' => 'El slug de la tarea ya existe',
                'description.required' => 'Este campo es obligatorio',
            ]
        );

        $manager_role = Role::find(1); // GET 'GERENTE' ROLE

        $permission = Permission::create([
            'name' => strtolower($request->slug),
            'display_name' => $request->display_name,
            'description' => $request->description,
            'module_id' => $request->module_id,
            'position' => Permission::max('position') + 1,
        ]);

        $manager_role->givePermissionTo($permission);

        if (isset($request->checkbox_view) ||isset($request->checkbox_create) || isset($request->checkbox_edit) || isset($request->checkbox_delete)) {
            if(isset($request->checkbox_view)) {
                $permission_slug = strtolower("$request->slug-view");
                $view_permission = Permission::create([
                    'name' => $permission_slug,
                    'module_id' => $request->module_id,
                    'parent_id' => $permission->id,
                    'position' => Permission::max('position') + 1,
                ]);
                $manager_role->givePermissionTo($view_permission);
            }

            if(isset($request->checkbox_create)) {
                $permission_slug = strtolower("$request->slug-create");
                $create_permission = Permission::create([
                    'name' => $permission_slug,
                    'module_id' => $request->module_id,
                    'parent_id' => $permission->id,
                    'position' => Permission::max('position') + 1,
                ]);
                $manager_role->givePermissionTo($create_permission);
            }

            if(isset($request->checkbox_edit)) {
                $permission_slug = strtolower("$request->slug-edit");
                $edit_permission = Permission::create([
                    'name' => $permission_slug,
                    'module_id' => $request->module_id,
                    'parent_id' => $permission->id,
                    'position' => Permission::max('position') + 1,
                ]);
                $manager_role->givePermissionTo($edit_permission);
            }

            if(isset($request->checkbox_delete)) {
                $permission_slug = strtolower("$request->slug-delete");
                $delete_permission = Permission::create([
                    'name' => $permission_slug,
                    'module_id' => $request->module_id,
                    'parent_id' => $permission->id,
                    'position' => Permission::max('position') + 1,
                ]);
                $manager_role->givePermissionTo($delete_permission);
            }

            $parent = Permission::find($permission->id);
            $parent->is_parent = true;
            $parent->save();
        }

        return response()->json(['success' => true]);
    }

    public function getPermission(Request $request)
    {
        $permissions = [
            'view' => false
        ];

        $roles = new SettingRole();
        $roles = $roles::all();

        $task = new SettingModulePermission();
        $id = $request->task_id;

        $data = $task->find($id);
        $children = $task::where('is_parent', false)->where('parent_id', $id)->get();

        foreach ($roles AS $role) {
            $check_role = Role::find($role->id);
            $permissions[$role->id]['id'] = $role->id;
            $permissions[$role->id]['name'] = $role->name;
            $permissions[$role->id]['options'][$data->name] = $check_role->hasPermissionTo($data->name);

            foreach ($children AS $child) {
                $permission = explode('-', $child['name']);
                $permission = end($permission);

                $permissions[$role->id]['options'][$child['name']] = $check_role->hasPermissionTo($child['name']);
            }
        }

        $data = [
            'task' => $data,
            'permissions' => $permissions,
        ];

        return response()->json($data);
    }

    public function editPermission(Request $request)
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

        $task = SettingModulePermission::find($task_id);
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

                    $permissions = new SettingRoleHasPermission();

                    $permissions->setting_permissions_id = $task_id;
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
        SettingRoleHasPermission::where('setting_permissions_id', $task_id)->delete();
    }

    public function assignPermission(Request $request)
    {
        $assign               = (bool)$request->assign;
        $parent_permission_id = $request->parent_permission_id;
        $permission_slug      = $request->slug;

        $role = Role::find($request->role_id);

        // Parent permissions
        $parent      = Permission::find($parent_permission_id);
        $parent_slug = $parent->name;

        if($assign) {
            $success = $role->givePermissionTo($permission_slug);
        } else {
            $success = $role->revokePermissionTo($permission_slug);
        }

        if($success) {
            // Check if role has access to parent permission

            $access_to_parent = false;

            // Children permissions
            $children_permissions = Permission::where('parent_id', $parent_permission_id)->get();

            // Check if role has at least access to one of children permissions
            foreach ($children_permissions AS $permission) {
                if($role->hasPermissionTo($permission->name)) {
                    $access_to_parent = true;
                    break;
                }
            }

            if($access_to_parent) {
                $role->givePermissionTo($parent_slug);
            } else {
                $role->revokePermissionTo($parent_slug);
            }
        }

        return response()->json($success);
    }

    public function editPermissionDescription(Request $request)
    {
        $this->validate($request,
            [
                'description' => 'required'
            ],
            [
                'description.required' => 'Este campo es obligatorio',
            ]
        );

        $permission = Permission::find($request->permission_id);
        $permission->description = $request->description;
        $permission->save();
    }

    public function getRolesByPermissions($permission_slug)
    {
        return Permission::whereName($permission_slug)->first()->roles->pluck('name');
    }
}
