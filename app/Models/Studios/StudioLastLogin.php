<?php

namespace App\Models\Studios;

use App\User;
use Illuminate\Database\Eloquent\Model;

class StudioLastLogin extends Model
{
    protected $table = 'studios_last_login';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
