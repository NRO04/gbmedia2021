<?php

namespace App\Models\Satellite;

use App\Models\Globals\City;
use App\Models\Satellite\SatelliteOwnerPaymentInfo;
use App\User;
use Illuminate\Database\Eloquent\Model;

class SatelliteOwner extends Model
{
    protected $guarded = ['id'];

    function paymentInfo(){
    	return $this->hasOne(SatelliteOwnerPaymentInfo::class, 'owner');
    }

    function city(){
        return $this->belongsTo(City::class, 'city_id');
    }

    function manager(){
        return $this->belongsTo(User::class, 'user_manager');
    }
}
