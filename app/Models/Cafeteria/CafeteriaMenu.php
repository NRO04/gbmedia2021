<?php

namespace App\Models\Cafeteria;

use Illuminate\Database\Eloquent\Model;

class CafeteriaMenu extends Model
{
    protected $guarded = [];

    public function cafeteriaType()
    {
        return $this->belongsTo(CafeteriaType::class);
    }

    public function orders()
    {
        return $this->hasMany(CafeteriaOrder::class);
    }
}
