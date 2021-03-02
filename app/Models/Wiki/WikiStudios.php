<?php

namespace App\Models\Wiki;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static updateOrCreate(array $array)
 * @method static where(string $string, string $string1, $wiki)
 * @method static create(array $array)
 */
class WikiStudios extends Model
{
    protected $connection = "external";
    protected $table = "wiki_studios";
    protected $guarded = ['id'];
}
