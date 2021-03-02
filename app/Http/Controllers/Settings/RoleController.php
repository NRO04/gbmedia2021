<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Settings\SettingRole;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getAllPermissions() {
        $permissions = [];
        foreach (Permission::all() as $permission) {
            if (Auth::user()->can($permission->name)) {
                $permissions[] = $permission->name;
            }
        }
        return response()->json($permissions);
    }

    public function getUserPermission($permission) {
        return response()->json(Auth::user()->can($permission));
    }

    public function list()
    {
        return view('adminModules.setting.role.list');
    }

    public function AllRoles()
    {
        $roles = SettingRole::where('name', '!=', 'sin rol')->select('id', 'name')->orderBy('name', 'asc')->get();
        return response()->json(['roles' => $roles]);
    }

    public function getRoles(Request $request)
    {
        if ($request->ajax()) {
            //see, edit, delete
            $permission = [true, true, true];

            $roles = SettingRole::where('name', '!=', 'sin rol')->orderBy('name', 'asc')->get();

            return DataTables::of($roles)
                ->addIndexColumn()
                ->addColumn('actions', function($row) {
                    $user = Auth::user();

                    $btn_edit   = "";
                    $btn_delete = "";

                    if ($user->can('roles-edit')) {
                        $btn_edit = "<button type='button' class='btn btn-sm btn-warning' onclick='Edit(".$row->id.")'><i class='fa fa-edit'></i></button>";
                    }

                    if ($user->can('roles-delete')) {
                        $btn_delete = "<button type='button' class='btn btn-sm btn-danger' onclick='Delete(".$row->id.")'><i class='fa fa-trash'></i></button>";
                    }

                    return "$btn_edit $btn_delete";
                })
                ->rawColumns(['actions'])
                ->make(true);
        }
    }

    public function saveRole(Request $request)
    {
        $this->validate($request,
            [
                'name' => 'required|unique:setting_roles,name|max:128'
            ],
            [
                'name.required' => 'Este campo es obligatorio',
                'name.unique' => 'El nombre del rol ya existe'
            ]
        );

        $role = new SettingRole();
        $role->name = $request->name;
        $role->alternative_name = $request->alternative_name;
        $role->position = SettingRole::max('position') + 1;
        $success = $role->save();

        return response()->json(['success' => $success]);
    }

    public function getRole(Request $request)
    {
        $role = new SettingRole();
        $id = $request->id;

        $data = $role->find($id);

        return response()->json($data);
    }

    public function editRole(Request $request)
    {
        $this->validate($request,
            [
                'name' => 'required',
            ],
            [
                'name.required' => 'Debe ingresar el nombre de la tarea',
            ]
        );

        $id = $request->id;

        $role = SettingRole::find($id);
        $role->name = $request->name;
        $role->alternative_name = $request->alternative_name;
        $success = $role->save();

        return response()->json(['success' => $success]);
    }

    public function deleteRole(Request $request)
    {
        try
        {
            $id = $request->id;
            $success = SettingRole::where('id', $id)->delete();
            return response()->json(['success' => $success]);
        }
        catch (\Exception $exception)
        {
            return response()->json(['success' => false, 'msg' => 'El rol no puede ser eliminado; tiene asociado un registro en otro módulo.', 'code' => $exception->getCode()], 500);
        }
    }
}
