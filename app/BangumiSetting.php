<?php

namespace App;

use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BangumiSetting extends Model
{
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED  = 1;

    use SoftDeletes;
    //
    protected $fillable = [
        'sitedriver',
        'username',
        'password',
        'status',
    ];

    protected $appends = ['tags'];

    private $avaliableSites = [
        'AcgRip'     => 'ACG.RIP',
        'MoeBangumi' => '萌番组',
        'Dmhy'       => '动漫花园',
        'Nyaa'       => 'Nyaa',
        'Kisssub'    => '爱恋动漫BT下载',
    ];

    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = app()->make(Encrypter::class)->encrypt($value, false);
        }
    }

    public function getPasswordAttribute($value)
    {
        return app()->make(Encrypter::class)->decrypt($value, false);
    }

    public function setSitedriverAttribute($value)
    {
        $avaliableDriver = array_keys($this->avaliableSites);
        if (in_array($value, $avaliableDriver)) {
            $this->attributes['sitedriver'] = $value;
            $this->attributes['sitename']   = $this->avaliableSites[$value];
        }
    }

    public function getTagsAttribute()
    {
        return $this->tags()->pluck('name');
    }

    public function tags()
    {
        return $this->belongsToMany(BangumiSettingTag::class, null, 'setting_id', 'tag_id');
    }
}
