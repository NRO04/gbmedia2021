<?php

namespace App\Models\RoomsControl;

use App\User;
use Illuminate\Database\Eloquent\Model;

class RoomsControl extends Model
{
    protected $guarded = [];

    function monitorName()
    {
        return $this->hasOne(User::class);
    }
}
