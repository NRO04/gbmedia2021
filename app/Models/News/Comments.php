<?php

namespace App\Models\News;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 * @method static join(string $string, string $string1, string $string2, string $string3)
 * @method static where(string $string, $commentId)
 */
class Comments extends Model
{
    protected $connection = "external";
    protected $table = "news_comments";
    protected $guarded = ["id"];

    public function replies()
    {
        return $this->hasMany(Comments::class, 'id', 'reply_id');
    }

}
