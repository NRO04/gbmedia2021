<?php

namespace App\Models\Cafeteria;

use Illuminate\Database\Eloquent\Model;

class CafeteriaBreakfastType extends Model
{
    protected $guarded = [];

    public function breakfastCategory()
    {
        return $this->belongsTo(CafeteriaBreakfastCategory::class);
    }
}
