<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BangumiTransferLog extends Model
{
    //
    protected $fillable = ['post_id', 'site', 'sitedriver', 'site_id', 'log_file'];
}
