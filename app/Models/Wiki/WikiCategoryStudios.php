<?php

namespace App\Models\Wiki;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static updateOrCreate(array $array)
 * @method static create(array $array)
 */
class WikiCategoryStudios extends Model
{
    protected $connection = "external";
    protected $table = "wiki_categories_studios";
    protected $guarded = ['id'];
}
