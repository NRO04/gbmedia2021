<?php

namespace App\Models\Satellite;

use Illuminate\Database\Eloquent\Model;
use App\User;

class SatelliteAccountLog extends Model
{
    protected $table = 'satellite_accounts_logs';
//    public $timestamps = false;
    protected $guarded = ['id'];
    
    public function created_by_user(){
    	return $this->belongsTo(User::class, 'created_by');
    }
}
