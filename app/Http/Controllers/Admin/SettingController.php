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

    public function createBangumiSettings(Request $request)
    {
        $request->validate([
            'sitedriver' => 'required',
            'username'   => 'required',
            'password'   => 'required',
            'status'     => 'required',
        ]);

        $setting = new BangumiSetting($request->all());
        $setting->save();

        return response([], 201);
    }

    public function updateBangumiSettings(Request $request, $id)
    {
        $setting = BangumiSetting::findOrFail($id);
        $request->validate([
            'sitedriver' => 'required',
            'username'   => 'required',
            'status'     => 'required',
        ]);

        $setting->fill($request->toArray());
        $setting->save();
        return response([], 200);
    }

    public function changeBangumiSettingStatus(Request $request, int $id)
    {
        $setting = BangumiSetting::findOrFail($id);
        $status  = $request->post('status');

        $setting->status = $status;
        $setting->save();
        return response([], 200);
    }

    public function deleteBangumiSettings(Request $request, $id)
    {
        $setting = BangumiSetting::findOrFail($id);
        $setting->delete();
        return response([], 204);
    }
}
