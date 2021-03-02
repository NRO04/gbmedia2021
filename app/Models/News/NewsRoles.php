<?php

namespace App\Models\News;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 * @method static where(string $string, $id)
 * @method static select()
 * @method static updateOrCreate(array $array)
 */
class NewsRoles extends Model
{
    protected $connection = "external";
    protected $table = "news_roles";
    protected $guarded = ['id'];
}
