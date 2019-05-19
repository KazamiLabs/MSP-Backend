<?php

namespace App;

use App\Events\UserCreating;
use App\Post;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;

class User extends Authenticatable
{
    use Notifiable;

    const TOKEN_REDIS_DB_INDEX = 1;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'nicename', 'email', 'password', 'status',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'avatar', 'password', 'remember_token',
    ];

    /**
     * 此模型的事件映射.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'creating' => UserCreating::class,
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

    public function setPasswordAttribute(string $password)
    {
        if (is_null($password) || $password === '') {
            return;
        }
        $this->attributes['password'] = Hash::make($password);
    }

    /**
     * Token 登记
     *
     * @param integer $ttl $token 有效期（分钟）
     * @param string $token 可选，不传会生成 token
     * @return string 返回 token （无论有没有定义 $token 参数）
     * @author Tsukasa Kanzaki <tsukasa.kzk@gmail.com>
     * @datetime 2019-05-11
     */
    public function setToken(int $ttl, string $token = null): string
    {
        // token 存储库选定
        Redis::select(self::TOKEN_REDIS_DB_INDEX);
        /**
         * token 为空
         * 则优先从 Redis 中查找，没有再生成
         */
        if (is_null($token)) {
            $token = Redis::get("user:id:{$this->id}");
            if (is_null($token)) {
                $token = md5($this->toJson() . time() . rand(10, 99));
            }
        }

        if ($ttl > 0) {
            Redis::set(
                "user:token:{$token}",
                $this->id,
                'EX',
                60 * $ttl
            );
            Redis::set(
                "user:id:{$this->id}",
                $token,
                'EX',
                60 * $ttl
            );
        } else {
            Redis::set("user:token:{$token}", $this->id);
            Redis::set("user:id:{$this->id}", $token);
        }
        // 使用完毕后重置到默认索引
        Redis::select(Config::get('database.redis.default.database'));
        return $token;
    }

    /**
     * Token 注销
     *
     * @return void
     * @author Tsukasa Kanzaki <tsukasa.kzk@gmail.com>
     * @datetime 2019-05-11
     */
    public function destroyToken()
    {
        // token 存储库选定
        Redis::select(self::TOKEN_REDIS_DB_INDEX);
        // 移除相应的键
        $token = Redis::get("user:id:{$this->id}");
        if ($token) {
            Redis::del("user:token:{$token}");
        }
        Redis::del("user:id:{$this->id}");
        // 使用完毕后重置到默认索引
        Redis::select(Config::get('database.redis.default.database'));
    }

    /**
     * 获取用户信息 (by token)
     * @param string $token
     * @return User|null
     */
    public static function findWithToken(string $token)
    {
        // token 存储库选定
        Redis::select(self::TOKEN_REDIS_DB_INDEX);
        $userId = Redis::get("user:token:{$token}");
        if (is_null($userId)) {
            return null;
        }
        // 使用完毕后重置到默认索引
        Redis::select(Config::get('database.redis.default.database'));
        return self::find($userId);
    }

}
