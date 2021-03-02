<?php

namespace App\Models\Alarms;

use App\Models\Settings\SettingRole;
use Illuminate\Database\Eloquent\Model;

class AlarmRole extends Model
{
    protected $guarded = [];

    public function role()
    {
        return $this->belongsTo(SettingRole::class);
    }
}
