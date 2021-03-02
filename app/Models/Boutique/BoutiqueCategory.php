<?php

namespace App\Models\Boutique;

use Illuminate\Database\Eloquent\Model;

class BoutiqueCategory extends Model
{
    protected $guarded = [];

    public function products()
    {
        return $this->hasMany(BoutiqueProduct::class);
    }
}
