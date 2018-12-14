<?php

namespace App\Http\Controllers\Admin;

use App\BangumiSetting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        $setting = BangumiSetting::find($id);
        if (is_null($setting)) {
            Log::info('找不到同步账户', ['id' => $id, 'action' => 'updateBangumiSettings']);
            abort(404, 'Settings not found');
        }
        $request->validate([
            'sitedriver' => 'required',
            'username'   => 'required',
            'status'     => 'required',
        ]);

        $setting->fill($request->toArray());
        $setting->save();
        return response([], 200);
    }

    public function deleteBangumiSettings(Request $request, $id)
    {
        $setting = BangumiSetting::find($id);
        if (is_null($setting)) {
            Log::info('找不到同步账户', ['id' => $id, 'action' => 'updateBangumiSettings']);
            abort(404, 'Settings not found');
        }
        $setting->delete();
        return response([], 204);
    }
}
