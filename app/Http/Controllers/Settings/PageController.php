<?php

namespace App\Http\Controllers\Settings;

use App\Models\Settings\SettingPage;
use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class PageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create(Request $request)
    {
        $this->validate($request,
        [
            'name' => 'required|unique:setting_locations,name',
            'rooms' => 'required|numeric',
        ],
        [
            'name.required' => 'Este campo es obligatorio',
            'name.unique' => 'Este nombre ya existe',
            'rooms.required' => 'Este campo es obligatorio',
        ]);

        try {
            DB::beginTransaction();

            $location = new SettingLocation();
            $location->name = $request->input('name');
            $location->position = 0;
            $location->rooms = $request->input('rooms');
            $location->base = 0;
            $location->save();

            $permission = new LocationPermissionController();
            $permission->create($location->id, $location->id);
            $permission->create(1, $location->id);

            DB::commit();
            return response()->json(['success' => true]);

        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function getLocations(SettingLocation $locations, Request $request)
    {
        $locations = $locations->get();
        $result = [];
        $edit_permission = true;
        $delete_permission = true;
        foreach ($locations as $location)
        {
            $btn_edit = ($edit_permission)? "<button type='button' class='btn btn-sm btn-warning' onclick='Edit(".$location->id.")'><i class='fa fa-edit'></i></button>" : "";
            $btn_delete = ($delete_permission)? "<button type='button' disabled class='btn btn-sm btn-danger' onclick='Delete(".$location->id.")'><i class='fa fa-trash'></i></button>" :
                "";
            $actions = $btn_edit." ".$btn_delete;

            $result[] = [
                "name" => $location->name,
                "rooms" => $location->rooms,
                "updated_at" => $location->updated_at->format('Y-m-d'),
                "actions" => $actions
            ];
        }

        return datatables($result)
        ->rawColumns(['actions'])
        ->toJson();
    }

    public function GetPermission($id)
    {
        $permission = SettingLocation::join("setting_location_permissions", "setting_locations.id" , "=", "setting_location_permissions.location_id")->select("setting_locations.id", "setting_locations.name")->where("setting_location_permissions.setting_location_id", "=" , $id)->get();
        return $permission;
    }

    public function infoEdit($id)
    {
        $location = SettingLocation::find($id);
        $locations = SettingLocation::all();
        $result = "<div class='col-lg-12'>
                        <div class='row'>";

        $result .= "<div class='col-lg-6'>
                        <input type='hidden' name='edit_id' id='edit_id' value='".$location->id."'/>
                        <div class='form-group'>
                            <label>Nombre</label>
                                <input type='text' class='form-control' name='edit_name' id='edit_name' value='".$location->name."'/>
                        </div>
                   </div>";

        $result .= "<div class='col-lg-6'>
                        <div class='form-group'>
                            <label>Nro Cuartos</label>
                            <input type='text' class='form-control' name='edit_rooms' id='edit_rooms' value='".$location->rooms."'/>
                        </div>
                   </div>";
        $result .= "</div></div>";

        $result .= "<div class='col-lg-12'><small class='text-warning'>Estas son las locaciones que podrá ver esta locación</small></div>";
        $result .= "<div class='col-lg-12 mt-3'>
                        <div class='row'>";

        foreach ($locations as $value)
        {
            $location_id = $value->id;
            $checked = (DB::table('setting_location_permissions')
                        ->where('setting_location_id', '=', $id)
                        ->where('location_id', '=', $location_id)
                        ->exists())? "checked" : "";

            $result .= "<div class='col-lg-6 d-flex'>
                            <label for='name' class='col-lg-4' style='padding: 0px'>".$value->name."</label>
                            <div class='col-sm-6'>
                                <label class='c-switch c-switch-pill c-switch-label c-switch-success c-switch-sm'>
                                    <input type='checkbox' class='c-switch-input' ".$checked." name='permission[]' value='".$value->id."'/>
                                    <span class='c-switch-slider' data-checked='✓' data-unchecked='✕'></span>
                                </label>
                            </div>
                        </div>";
        }
        $result .= "</div></div>";
        return response($result);
    }

    public function update(Request $request)
    {
        $this->validate($request,
        [
            'edit_name' => 'required|unique:setting_locations,name,'.$request->edit_id,
            'edit_rooms' => 'required|numeric',
        ],
        [
            'edit_name.required' => 'Este campo es obligatorio',
            'edit_name.unique' => 'Este nombre ya existe',
            'edit_rooms.required' => 'Este campo es obligatorio',
        ]);

        try {

            DB::beginTransaction();

            $permission = new LocationPermissionController();

            $id = $request->input('edit_id');
            $permission->destroyAccess($id);
            $access = $request->input('permission');

            for ($i=0; $i < count($access) ; $i++)
            {
                $location_id = $access[$i];
                $permission->create($id, $location_id);
            }

            $location = SettingLocation::find($request->edit_id);
            $location->name = $request->input('edit_name');
            $location->rooms = $request->input('edit_rooms');
            $location->save();

            DB::commit();
            return response()->json(['success' => true]);

        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function destroy($id)
    {
        $setting = SettingLocation::find($id);
        $setting->delete();
    }

    //nuevas
    public function viewPages()
    {
        return view("adminModules.setting.page.list");
    }

    public function getPages()
    {
        $pages = SettingPage::all();
        foreach ($pages as $page)
        {
            $result[] = [
                "name" => $page->name,
                "create_task" => "",
                "task" => "",
                "edit" => "",
                "admin" => "",
            ];
        }
        return response()->json($result);
    }
}
