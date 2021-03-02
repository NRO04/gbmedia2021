<?php

namespace App\Models\Boutique;

use Illuminate\Database\Eloquent\Model;

class BoutiqueProduct extends Model
{
    protected $guarded = [];

    public function boutiqueCategory()
    {
        return $this->belongsTo(BoutiqueCategory::class);
    }

    public function boutiqueInventory()
    {
        return $this->hasMany(BoutiqueInventory::class, 'boutique_product_id');
    }

    public function boutiqueProductLogs()
    {
        return $this->hasMany(BoutiqueProductsLog::class, 'boutique_product_id')->orderBy('created_at', 'desc');
    }

}
