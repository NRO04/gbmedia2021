<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;

class SettingRoleHasPermission extends Model
{
    public $timestamps = false;

    protected $table = "setting_role_has_permissions";

    public function role()
    {
        return $this->belongsTo('App\Settings\SettingRole');
    }

    public function task()
    {
        return $this->belongsTo('App\Settings\SettingTask');
    }
}
