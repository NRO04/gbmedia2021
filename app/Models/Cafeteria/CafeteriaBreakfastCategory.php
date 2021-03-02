<?php

namespace App\Models\Cafeteria;

use Illuminate\Database\Eloquent\Model;

class CafeteriaBreakfastCategory extends Model
{
    protected $guarded = [];

    public function breakfastTypes()
    {
        return $this->hasMany(CafeteriaBreakfastType::class);
    }

}
