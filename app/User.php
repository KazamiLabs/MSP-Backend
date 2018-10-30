<?php

namespace App;

use App\Post;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function scopeSearchSelect($query)
    {
        return $query->select('id', 'user_nicename');
    }

    public function scopeSearchCondition($query, $search_user)
    {
        return $query->where('user_login', 'like', "%{$search_user}%")
            ->whereOr('user_nicename', 'like', "%{$search_user}%")
            ->whereOr('user_email', 'like', "%{$search_user}%");
    }
}
