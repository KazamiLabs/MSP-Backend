<?php

namespace App\Http\Controllers\Admin;

use App\Post;
use App\BangumiTransferLog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BangumiController extends Controller
{
    //

    public function transferLog(Request $request, int $post_id)
    {
        $post = Post::findOrFail($post_id);
        return $post->bangumiTransferLogs()
            ->select('id', 'site', 'sync_state', 'log', 'created_at')
            ->get();
    }

    public function transferLogRaw(Request $request, int $id)
    {
        $log = BangumiTransferLog::findOrFail($id);
        return response(['contents' => $log->log]);
    }
}
