<?php

namespace App\Models\Studios;

use Illuminate\Database\Eloquent\Model;

class Studios extends Model
{
    public function lastLogin()
    {
        return $this->belongsTo(StudioLastLogin::class, 'id');
    }
}
