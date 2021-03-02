<?php

namespace App\Models\News;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static updateOrCreate(array $array)
 * @method static where(string $string, $id)
 */
class NewsStudio extends Model
{
    protected $connection = "external";
    protected $table = "news_studios";
    protected $guarded = ["id"];

}
