<?php

namespace App\Http\Controllers\Admin;

use App\BangumiTransferLog;
use App\Http\Controllers\Controller;
use App\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BangumiController extends Controller
{
    //

    public function transferLog(Request $request, int $post_id)
    {
        $post = Post::findOrFail($post_id);
        return $post->bangumiTransferLogs()
            ->select('id', 'site', 'sync_state', 'log_file', 'created_at')
            ->get();
    }

    public function transferLogRaw(Request $request, int $id)
    {
        $log      = BangumiTransferLog::findOrFail($id);
        $filepath = $log->log_file;
        // $contents = Storage::get($filepath);
        $contents = file_get_contents($log->log_file_path);
        return response(['contents' => $contents]);
    }
}
