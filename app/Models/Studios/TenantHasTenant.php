<?php

namespace App\Models\Studios;

use App\Models\Tenancy\Tenant;
use Illuminate\Database\Eloquent\Model;

class TenantHasTenant extends Model
{
    public function hasTenant()
    {
        return $this->belongsTo(Tenant::class, 'has_tenant_id');
    }
}
