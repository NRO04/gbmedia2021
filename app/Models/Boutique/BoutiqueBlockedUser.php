<?php

namespace App\Models\Boutique;

use App\User;
use Illuminate\Database\Eloquent\Model;

class BoutiqueBlockedUser extends Model
{
    protected $guarded = [];

    public function blockedUser()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function blockedByUser()
    {
        return $this->belongsTo(User::class);
    }
}
