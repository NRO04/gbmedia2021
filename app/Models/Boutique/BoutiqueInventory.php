<?php

namespace App\Models\Boutique;

use App\Models\Settings\SettingLocation;
use Illuminate\Database\Eloquent\Model;

class BoutiqueInventory extends Model
{
    protected $guarded = [];

    public function settingLocation()
    {
        return $this->belongsTo(SettingLocation::class);
    }
}
