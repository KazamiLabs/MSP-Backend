<?php

namespace App;

use App\Post;
use Illuminate\Database\Eloquent\Model;

class BangumiTransferLog extends Model
{
    //
    protected $fillable = ['post_id', 'site', 'sitedriver', 'site_id', 'log'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
