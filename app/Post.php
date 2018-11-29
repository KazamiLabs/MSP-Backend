<?php

namespace App;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class Post extends Model
{
    use SoftDeletes;
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'post_date',
        'post_modified',
    ];
    //
    public function author()
    {
        return $this->belongsTo(User::class, 'post_author');
    }

    public function bangumi()
    {
        return $this->hasOne();
    }

    public function getCreatedAtAttribute($value)
    {
        $user = Auth::user();
        //If no user is logged in, we'll just default to the
        //application's timezone
        $timezone = $user ? $user->timezone : Config::get('app.timezone');
        return Carbon::createFromTimestamp(strtotime($value))
            ->timezone($timezone)
        //Leave this part off if you want to keep the property as
        //a Carbon object rather than always just returning a string
            ->toDateTimeString()
        ;
    }

    public function getUpdatedAtAttribute($value)
    {
        $user     = Auth::user();
        $timezone = $user ? $user->timezone : Config::get('app.timezone');
        return Carbon::createFromTimestamp(strtotime($value))
            ->timezone($timezone)
            ->toDateTimeString()
        ;
    }

    public function getPostDateAttribute($value)
    {
        $user     = Auth::user();
        $timezone = $user ? $user->timezone : Config::get('app.timezone');
        return Carbon::createFromTimestamp(strtotime($value))
            ->timezone($timezone)
            ->toDateTimeString()
        ;
    }

    public function getPostModifiedAttribute($value)
    {
        $user     = Auth::user();
        $timezone = $user ? $user->timezone : Config::get('app.timezone');
        return Carbon::createFromTimestamp(strtotime($value))
            ->timezone($timezone)
            ->toDateTimeString()
        ;
    }
}
