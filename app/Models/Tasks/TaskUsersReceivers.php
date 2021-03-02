<?php

namespace App\Models\Tasks;

use App\User;
use Illuminate\Database\Eloquent\Model;

class TaskUsersReceivers extends Model
{
    protected $guarded = [];

    function user(){
    	return $this->belongsTo(User::class);
    }
}
