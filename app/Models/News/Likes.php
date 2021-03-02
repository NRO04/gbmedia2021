<?php

namespace App\Models\News;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 * @method static where(string $string, $id)
 * @method static join(string $string, string $string1, string $string2, string $string3)
 */
class Likes extends Model
{
    protected $connection = "external";
    protected $guarded = ['id'];
    protected $table = "news_likes";
}
