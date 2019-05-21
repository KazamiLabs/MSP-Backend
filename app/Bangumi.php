<?php

namespace App;

use App\Post;
use Illuminate\Database\Eloquent\Model;

class Bangumi extends Model
{

    const TORRENT_PATH = 'private/torrent';

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

    public static function getTorrentFullPath(string $torrentFilename): string
    {
        return self::TORRENT_PATH . '/' . $torrentFilename;
    }
}
