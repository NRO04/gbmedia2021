<?php

namespace App\Models\Chat;

use App\User;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = ['message'];

    function user(){
    	return $this->belongsTo(User::class);
    }
}
