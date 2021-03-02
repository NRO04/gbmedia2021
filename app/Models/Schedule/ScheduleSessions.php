<?php

namespace App\Models\Schedule;

use Illuminate\Database\Eloquent\Model;
use App\Models\Settings\SettingLocation;
use App\Models\Schedule\ScheduleSessionTypes;

/**
 * @method static where(string $string, $model_id)
 * @method static orderBy(string $string, string $string1)
 */
class ScheduleSessions extends Model
{
    public function location()
    {
        return $this->belongsTo(SettingLocation::class , 'setting_location_id');
    }

    public function type()
    {
        return $this->belongsTo(ScheduleSessionTypes::class , 'session');
    }
}
