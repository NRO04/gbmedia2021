<?php

namespace App\Models\Satellite;

use Illuminate\Database\Eloquent\Model;
use App\User;

class SatelliteContract extends Model
{
    public function modified_by_user(){
    	return $this->belongsTo(User::class, 'modified_by');
    }
}
