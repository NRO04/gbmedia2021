<?php

namespace App\Models\Tenancy;

use App\Models\Contracts\TenantHasContract;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

/**
 * @method static where(string $string, string $string1, int $int)
 * @method static find(mixed|\Stancl\Tenancy\Contracts\Tenant|null $tenant)
 * @method static select(string $string)
 */
class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    public static function getCustomColumns(): array
    {
        return [
            'id',
        ];
    }

    public function getIncrementing()
    {
        return true;
    }

    function contracts()
    {
        return $this->hasOne(TenantHasContract::class);
    }
}
