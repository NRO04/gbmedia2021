<?php

namespace App\Models\Settings;

use App\Models\Statistics\Statistics;
use Illuminate\Database\Eloquent\Model;

class SettingPage extends Model
{
    protected $guarded = ['id'];

    public function statistics()
    {
        return $this->hasMany(Statistics::class);
    }
}
