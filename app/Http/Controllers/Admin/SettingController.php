<?php

namespace App\Http\Controllers\Admin;

use App\BangumiSetting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    //

    public function bangumiSettings(Request $request)
    {
        $limit    = $request->get('limit', 15);
        $settings = BangumiSetting::select('id', 'sitename', 'sitedriver', 'username', 'status')->paginate($limit);
        return $settings;
    }

    public function updateBangumiSettings(Request $request, $id)
    {

    }
}
