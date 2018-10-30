<?php

namespace App\Http\Controllers;

use App\MessageLog;
use Illuminate\Http\Request;

class MessageLogsController extends Controller
{
    //

    public function uploadMessage(Request $request)
    {
        $message_log = new MessageLog();

        $message_log->anonymous         = serialize($request->input("anonymous"));
        $message_log->font              = $request->input("font");
        $message_log->src_group_id      = $request->input("group_id");
        $message_log->message           = serialize($request->input("message"));
        $message_log->message_id        = $request->input("message_id");
        $message_log->message_type      = $request->input("message_type");
        $message_log->post_type         = $request->input("post_type");
        $message_log->raw_message       = $request->input("raw_message");
        $message_log->self_id           = $request->input("self_id");
        $message_log->sub_type          = $request->input("sub_type");
        $message_log->received_time     = $request->input("time");
        $message_log->received_datetime = date("Y-m-d H:i:s", $request->input("time"));
        $message_log->user_id           = $request->input("user_id");

        $message_log->save();

        return response()->json([
            'status' => 'success',
        ]);
    }
}
