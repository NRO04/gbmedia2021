<?php

namespace App\Models\News;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 * @method static where(array[] $array)
 */
class Seen extends Model
{
    protected $connection = "external";
    protected $guarded = [];
    protected $table = "news_views";
}
