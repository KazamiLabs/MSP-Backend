<?php

namespace App\Http\Controllers\Admin;

use App\SysSetting;
use App\BangumiSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;

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

    public function sysSettingList(Request $request)
    {
        $keyword = $request->input('keyword');
        $list    = SysSetting::searchCondition($keyword)
            ->paginate(10);
        return $list;
    }

    public function sysSettings(Request $request)
    {
        return SysSetting::getValues();
    }

    public function setSysSetting(Request $request)
    {
        $settings = new Collection($request->all());

        if ($settings->isNotEmpty()) {
            $settings->each(function ($value, $key) {
                if (is_null($value)) {
                    $value = '';
                }
                SysSetting::setValue($key, $value);
            });
        }

        return response(null, 200);
    }
}
