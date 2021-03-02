<?php

namespace App\Models\News;

use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 * @method static findOrFail($id)
 * @method static join(string $string, string $string1, string $string2, string $string3)
 */
class News extends Model
{
    protected $connection = "external";
    protected $guarded = ["id"];

    public function likes()
    {
        return $this->hasMany(Likes::class);
    }

    public function comments()
    {
        return $this->hasMany(Comments::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
