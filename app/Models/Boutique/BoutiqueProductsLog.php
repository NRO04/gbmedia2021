<?php

namespace App\Models\Boutique;

use App\User;
use Illuminate\Database\Eloquent\Model;

class BoutiqueProductsLog extends Model
{
    protected $guarded = [];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
