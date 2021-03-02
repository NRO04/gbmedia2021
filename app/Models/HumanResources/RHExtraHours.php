<?php

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Model;

class RHExtraHours extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'rh_extra_hours';
    protected $fillable = [
        'user_id',
        'user_acep_den_id',
        'morning',
        'night',
        'total_money',
        'comment',
        'total_extras',
        'comment_note',
        'extra_reason',
        'state',
        'comment_denied',
        'application_date',
        'review_date',
        'range',
        'range_revision',
        'day',
        'month',
        'year',
        'created_at',
        'updated_at',
    ];

    public function RHExtraHoursToUsers()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function user_acep_den()
    {
        return $this->belongsTo('App\User', 'user_acep_den_id');
    }
}
