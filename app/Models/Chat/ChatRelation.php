<?php

namespace App\Models\Chat;

use App\User;
use App\Models\Chat\Chat;
use Illuminate\Database\Eloquent\Model;

class ChatRelation extends Model
{
    function chat(){
    	return $this->belongsTo(Chat::class);
    }

    function user(){
    	return $this->belongsTo(User::class);
    }
}
