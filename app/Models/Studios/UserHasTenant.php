<?php

namespace App\Models\Studios;

use App\Models\Tenancy\Tenant;
use App\User;
use Illuminate\Database\Eloquent\Model;

class UserHasTenant extends Model
{
    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function toTenant()
    {
        return $this->belongsTo(Tenant::class, 'to_tenant_id')->orderBy('data->studio_name');
    }
}
