<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;
use App\Models\Settings\SettingLocation;

class SettingLocationPermission extends Model
{
    function Location(){
    	return $this->belongsTo(SettingLocation::class);
    }
}
