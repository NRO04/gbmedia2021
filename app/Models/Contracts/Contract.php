<?php

namespace App\Models\Contracts;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    public function roles()
    {
        return $this->hasMany(RoleHasContract::class, 'contract_id');
    }
}
