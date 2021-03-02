<?php

namespace App\Models\Contracts;

use Illuminate\Database\Eloquent\Model;

class RoleHasContract extends Model
{
    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
}
