<?php

namespace App\Models\Users;

use App\User;
use Illuminate\Database\Eloquent\Model;

class UserDocument extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
