<?php

namespace App\Models\Satellite;

use Illuminate\Database\Eloquent\Model;
use App\User;

class SatelliteAccountNote extends Model
{
    protected $table = 'satellite_accounts_notes';

    public function created_by_user(){
    	return $this->belongsTo(User::class, 'created_by');
    }
}
