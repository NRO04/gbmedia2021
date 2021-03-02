<?php

namespace App\Models\Cafeteria;

use App\User;
use Illuminate\Database\Eloquent\Model;

class CafeteriaOrder extends Model
{
    protected $guarded = [];

    public function cafeteriaMenu()
    {
        return $this->belongsTo(CafeteriaMenu::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
