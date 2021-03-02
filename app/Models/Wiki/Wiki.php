<?php

namespace App\Models\Wiki;

use App\Notifications\PostCreated;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;

/**
 * @method belongsTo(string $class)
 * @method static join(string $string, string $string1, string $string2, string $string3)
 * @method static where(array $compact)
 * @method static create(array $array)
 * @method static findOrFail($id)
 * @method static updateOrCreate(array $array)
 * @method static orderBy(string $string, string $string1)
 * @method static select(string $string, string $string1)
 */
class Wiki extends Model
{
    protected $connection = "external";
    protected $table = "wikis";
    protected $guarded = ['id'];


    public static function boot()
    {
        parent::boot();

        static::created(function ($model){
            $usuarios = User::all();

            $users = $usuarios->filter(function($user){
                return $user->where('setting_role_id', '=', 12);
            });

            Notification::send($users, new PostCreated($model));
        });
    }

    public function category()
    {
        return $this->belongsTo(WikiCategory::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'wiki_users', 'wiki_id', 'user_id');
    }

    public function roles()
    {
        return $this->hasMany(WikiRole::class);
    }

    public function studios()
    {
        return $this->hasMany(WikiStudios::class);
    }

    public function scopefindByAuthor($author)
    {
        return static::where(compact('author'))->first();
    }

    public function scopefindById($id)
    {
        return static::where(compact('id'))->first();
    }

    public function scopefindByDate($created_at)
    {
        return static::where(compact('created_at'))->first();
    }

    public function scopefindByCategory($category_name)
    {
        return static::where(compact('category_name'))->first();
    }
}
