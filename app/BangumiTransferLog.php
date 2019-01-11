<?php

namespace App;

use App\Post;
use Illuminate\Database\Eloquent\Model;

class BangumiTransferLog extends Model
{
    //
    protected $fillable = ['post_id', 'site', 'sitedriver', 'site_id', 'log_file'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function setLogFileAttribute($filepath)
    {
        $this->attributes['log_file'] = str_replace(\storage_path() . DIRECTORY_SEPARATOR, '', $filepath);
    }

    public function getLogFileAttribute($filepath)
    {
        return \storage_path($filepath);
    }
}
