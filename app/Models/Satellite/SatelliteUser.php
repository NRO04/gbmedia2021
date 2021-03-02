<?php

namespace App\Models\Satellite;

use Illuminate\Database\Eloquent\Model;
use App\Models\Globals\GlobalCountry;
use App\User;

class SatelliteUser extends Model
{
    protected $guarded = ['id'];
    public function document(){
    	return $this->belongsTo(SatelliteUsersDocumentsType::class, 'document_type');
    }

    public function country(){
    	return $this->belongsTo(GlobalCountry::class, 'country_id');
    }

    public function created_by_user(){
    	return $this->belongsTo(User::class, 'created_by');
    }

    public function modified_by_user(){
    	return $this->belongsTo(User::class, 'modified_by');
    }
}
