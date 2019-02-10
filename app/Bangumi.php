<?php

namespace App;

use App\Post;
use Illuminate\Database\Eloquent\Model;

class Bangumi extends Model
{
    //
    protected $fillable = ['filename', 'filepath', 'group_name', 'title', 'year'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function getEncodeFilenameAttribute()
    {
        return urlencode($this->filename);
    }
}
