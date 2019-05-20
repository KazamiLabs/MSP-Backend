<?php

namespace App;

use App\User;
use Carbon\Carbon;
use App\BangumiSetting;
use App\Events\PostCreating;
use App\Events\PostUpdating;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;

    const SHOW_QUEUE_KEY = 'posts_sync:queues';

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'post_date',
        'post_modified',
    ];
    protected $fillable = [
        'post_content',
        'post_title',
        'post_excerpt',
        'post_status',
        'comment_status',
        'ping_status',
        // 'post_password',
        // 'post_name',
        'to_ping',
        'pinged',
        'post_date',
        'post_date_gmt',
        'post_modified',
        'post_modified_gmt',
        'post_content_filtered',
        'post_parent',
        // 'guid',
        'menu_order',
        'post_type',
        // 'post_mime_type',
    ];

    protected $hidden = [
        'post_password',
        'to_ping',
        'pinged',
        'ping_status',
        'guid',
        'menu_order',
        'post_parent',
        'deleted_at',
    ];

    /**
     * 此模型的事件映射.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'creating' => PostCreating::class,
        'updating' => PostUpdating::class,
    ];

    //
    public function author()
    {
        return $this->belongsTo(User::class, 'post_author');
    }

    public function bangumi()
    {
        return $this->hasOne(Bangumi::class);
    }

    public function bangumiTransferLogs()
    {
        return $this->hasMany(BangumiTransferLog::class);
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

    public function getQueueKey(BangumiSetting $setting): string
    {
        return self::SHOW_QUEUE_KEY . ":setting:{$setting->id}:post:{$this->id}";
    }

    public static function getQueueListKey(): string
    {
        return self::SHOW_QUEUE_KEY . ":*";
    }
}
