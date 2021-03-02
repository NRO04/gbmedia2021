<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;

class SettingModulePermission extends Model
{
    public function module()
    {
        return $this->belongsTo(SettingModule::class);
    }

    public function role_has_module_permission()
    {
        return $this->hasMany(SettingRoleHasPermission::class, 'setting_permissions_id');
    }
}
