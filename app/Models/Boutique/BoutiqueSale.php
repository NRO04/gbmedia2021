<?php

namespace App\Models\Boutique;

use App\Models\Settings\SettingLocation;
use App\User;
use Illuminate\Database\Eloquent\Model;

class BoutiqueSale extends Model
{
    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(BoutiqueProduct::class, 'boutique_product_id');
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_user_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_user_id');
    }

    public function location()
    {
        return $this->belongsTo(SettingLocation::class, 'setting_location_id');
    }
}
