<?php

namespace App\Models\Chat;

use App\User;
use App\Models\Chat\ChatRelation;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    function relation(){
    	return $this->hasMany(ChatRelation::class);
    }

    function user(){
    	return $this->belongsTo(User::class);
    }
}
