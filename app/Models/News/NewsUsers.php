<?php

namespace App\Models\News;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static updateOrCreate(array $array)
 */
class NewsUsers extends Model
{
    protected $connection = "external";
    protected $table = "news_users";
    protected $guarded = ['id'];
}
