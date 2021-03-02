<?php

namespace App\Models\Maintenance;

use App\Models\Settings\SettingLocation;
use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    protected $table = 'maintenances';

    public function settingLocation()
    {
        return $this->belongsTo(SettingLocation::class);
    }
}
