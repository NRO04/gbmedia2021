<?php

namespace App\Models\Alarms;

use Illuminate\Database\Eloquent\Model;

class Alarm extends Model
{
    protected $guarded = [];

    public function roles()
    {
        return $this->hasMany(AlarmRole::class);
    }

    public function users()
    {
        return $this->hasMany(AlarmUser::class);
    }
}
