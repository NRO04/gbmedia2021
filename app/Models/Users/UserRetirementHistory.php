<?php

namespace App\Models\Users;

use App\User;
use Illuminate\Database\Eloquent\Model;

class UserRetirementHistory extends Model
{
    protected $guarded = [];
    protected $table = 'user_retirement_history';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
