<?php

namespace App\Models\Tasks;

use App\Models\Settings\SettingRole;
use Illuminate\Database\Eloquent\Model;

class TaskRolesReceivers extends Model
{
    function role(){
    	return $this->belongsTo(SettingRole::class, 'setting_role_id');
    }
}
