<?php

namespace App\Models\Boutique;

use App\User;
use Illuminate\Database\Eloquent\Model;

class BoutiqueInventoryIngress extends Model
{
    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(BoutiqueProduct::class, 'boutique_product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
