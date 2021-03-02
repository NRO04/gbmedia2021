<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Settings\SettingLocationPermission;
use Illuminate\Support\Facades\DB;

class LocationPermissionController extends Controller
{
	public function create($id, $location_id)
    {
        $permission = new SettingLocationPermission();

        $permission->setting_location_id = $id;
        $permission->location_id = $location_id;
        $permission->save();
    }

    public function destroy($id)
    {
        $setting = SettingLocation::find($id);
        $setting->delete();
    }

    public function destroyAccess($id)
	{
	    DB::table('setting_location_permissions')->where('setting_location_id', "=" ,$id)->delete();
	}
}
