<?php

namespace App;

use App\Post;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
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
        'avatar', 'password', 'remember_token',
    ];

    protected $appends = ['avatar_addr'];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function scopeSearchSelect($query)
    {
        return $query->select('id', 'nicename', 'avatar');
    }

    public function scopeSearchCondition($query, $search_user)
    {
        return $query->where('name', 'like', "%{$search_user}%")
            ->whereOr('email', 'like', "%{$search_user}%")
            ->whereOr('nicename', 'like', "%{$search_user}%")
            ->whereOr('email', 'like', "%{$search_user}%");
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getAvatarAddrAttribute()
    {
        $url = '';
        if (filter_var($this->avatar, FILTER_VALIDATE_URL) === false) {
            $url = route('user.avatar', ['id' => $this->id]);
        } else {
            $url = $this->avatar;
        }
        return $url;
    }

}
