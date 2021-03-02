<?php

namespace App;

use App\Models\Attendance\Attendance;
use App\Models\Bookings\Booking;
use App\Models\Globals\Bank;
use App\Models\News\Comments;
use App\Models\Chat\ChatMessage;
use App\Models\Settings\SettingLocation;
use App\Models\Settings\SettingRole;
use App\Models\Statistics\Statistics;
use App\Models\Users\UserDocument;
use App\Models\Wiki\Wiki;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

/**
 * @method belongsTo(string $class, string $string)
 * @method hasMany(string $class)
 * @method static select(string $string)
 * @method static where(string $string, $model_id)
 * @method static join(string $string, string $string1, string $string2)
 * @method static leftjoin(string $string, string $string1, string $string2, string $string3)
 * @method static findOrFail($user_id)
 * @property  contract_date
 */
class User extends Authenticatable
{
    use Notifiable;
    use HasRoles;

    protected $guarded = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guard = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token',];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['email_verified_at' => 'datetime',];

    public function contractDate()
    {
        return Carbon::createFromFormat('d/m/Y', $this->contract_date);
    }

    public function rolePermissions()
    {
        return $this->belongsTo(Role::class, 'setting_role_id');
    }

    public function location()
    {
        return $this->belongsTo(SettingLocation::class, 'setting_location_id');
    }

    public function comments()
    {
        return $this->hasMany(Comments::class);
    }

    public function posts()
    {
        return $this->hasMany(Wiki::class);
    }

    public function completed()
    {
        return $this->hasMany(Completed::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function userFullName()
    {
        $full_name = $this->first_name." ".$this->last_name." ".$this->second_last_name;
        return $full_name;
    }

    public function roleUserFullName()
    {
        $full_name = ($this->setting_role_id == 14)? $this->nick : $this->first_name." ".$this->last_name." ".$this->second_last_name;
        return $full_name;
    }

    public function roleUserShortName()
    {
        return ($this->setting_role_id == 14) ? $this->nick : "$this->first_name $this->last_name";
    }

    public function role()
    {
        return $this->belongsTo(SettingRole::class, 'setting_role_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    public function documents()
    {
        return $this->hasMany(UserDocument::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_account_id');
    }

    public function statistics()
    {
        return $this->hasMany(Statistics::class);
    }
}
