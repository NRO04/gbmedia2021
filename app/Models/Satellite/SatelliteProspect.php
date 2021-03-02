<?php

namespace App\Models\Satellite;

use App\Models\Globals\City;
use Illuminate\Database\Eloquent\Model;

class SatelliteProspect extends Model
{
    protected $guarded = ['id'];

    function city(){
        return $this->belongsTo(City::class, 'city_id');
    }

}
