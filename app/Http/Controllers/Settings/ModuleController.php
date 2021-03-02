<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Settings\SettingModule;
use DataTables;
use Illuminate\Support\Facades\Auth;

class ModuleController extends Controller
{

    public function create(Request $request)
    {
        $count = SettingModule::where('name', $request->input('name'))
                ->count();
        $success = true;

        if ($count == 0):

            $SettingModule = new SettingModule();
            $SettingModule->name = $request->input('name');
            $SettingModule->description = $request->input('description');
            $SettingModule->is_admin = 1;
            $success = $SettingModule->save();

            return response()
                ->json(['success' => $success,'message' => true ]);

        elseif ($count > 0):

            return response()
                ->json(['success' => $success,'message' => false ]);
        endif;

    }

    public function getModule(Request $request)
    {
        $Module = new SettingModule();
        $id = $request->id;
        $data = $Module->find($id);
        return response()->json($data);
    }

    public function getModules(Request $request)
    {
        $data = SettingModule::where('id', '!=', 1)->orderBy('name', 'ASC')->get();

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('description', function ($row) {
                return "<button type='button' class='btn btn-sm btn-info' data-toggle='tooltip' data-placement='top' title='$row->description'><i class='fa fa-info-circle' aria-hidden='true'></i></button>";
            })
            ->addColumn('admin', function ($row) {
                if(!Auth::user()->can('module-edit')) {
                    $admin = "";
                } else {
                    $admin = "<button type='button' class='btn btn-sm btn-danger'><i class='fa fa-times' aria-hidden='true'></i></button>";

                    if ($row->is_admin == 1) {
                        $admin = "<button type='button' class='btn btn-sm btn-success'><i class='fas fa-check'></i></button>";
                    }
                }

                return $admin;
            })
            ->addColumn('permissions', function ($row) {
                return Auth::user()->can('module-edit') ? "<a href=" . route('permission.module_permission', $row->id) . " target='_blank' class='btn btn-sm btn-success'>Permisos &nbsp;<i class='fa fa-external-link-alt'></i></a>" : "";
            })
            ->addColumn('edit', function ($row) {
                return Auth::user()->can('module-edit') ? "<button onclick='update_module($row->id)' type='button' class='btn btn-sm btn-warning' data-toggle='modal' data-target='exampleModal' style='margin-right: 3px;'><i class='fa fa-edit'></i></button>" : '';
            })
            ->rawColumns(['description', 'admin', 'permissions', 'edit'])
            ->make(true);
    }

    public function update(Request $request)
    {
        $row = $request->input();

        $count = SettingModule::where('name',$request->input('name_update'))
                ->where('name','<>',$request->input('old_name_update'))
                ->count();

        $success = true;

        if ($count == 0):

            $SettingModule = SettingModule::find($row['id_update']);
            $SettingModule->name = $row['name_update'];
            $SettingModule->description = $row['description_update'];

            if(isset($row['is_admin_update'])){
                $SettingModule->is_admin = 1;
            }else{
                $SettingModule->is_admin = 0;
            }

            $success = $SettingModule->save();


            return response()
                ->json(['success' => $success,'message' => true ]);

        elseif ($count > 0):

            return response()
                ->json(['success' => $success,'message' => false ]);
        endif;
    }

    public function destroy(Request $request, $id)
    {
        SettingModule::where('id', $id)->delete();
        return redirect()->back()->with('delete', 'El módulo ha sido Eliminado exitosamente');
    }
}
