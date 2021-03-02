<?php

namespace App\Models\Wiki;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static where(string $string, $post_id)
 */
class WikiPostCategory extends Model
{
    protected $connection = "external";
    protected $table = "wiki_has_category";
    protected $fillable = ['wiki_category_id', 'wiki_id'];
}
