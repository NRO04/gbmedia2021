<?php

namespace App\Models\Wiki;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 * @method static updateOrCreate(array $array)
 * @method static where(string $string, $id)
 * @method static join(string $string, string $string1, string $string2, string $string3)
 * @method static select(string $string)
 */
class WikiRole extends Model
{
    protected $connection = "external";
    protected $table = "wiki_roles";
    protected $guarded = ['id'];

    public function posts()
    {
        return $this->hasMany(Wiki::class, 'wiki_id');
    }
}
