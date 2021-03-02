<?php

namespace App\Models\Wiki;

use Illuminate\Database\Eloquent\Model;

/**
 * @method hasMany(string $class)
 * @method static create(array $array)
 * @method static select(string $string)
 * @method static findOrFail($id)
 * @method static where(string $string, string $string1, $name)
 * @method static createOrUpdate(array $array)
 * @method static updateOrCreate(array $array)
 * @method static orderBy(string $string, string $string1)
 */
class WikiCategory extends Model
{
    protected $connection = "external";
    protected $table = "wiki_categories";
    protected $guarded = ['id'];

    public function posts()
    {
        return $this->hasMany(Wiki::class);
    }
}
