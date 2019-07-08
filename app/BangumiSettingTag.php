<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BangumiSettingTag extends Model
{
    //

    protected $fillable = ['name'];

    public function settings()
    {
        return $this->belongsToMany(BangumiSetting::class, null, 'tag_id', 'setting_id');
    }
}
