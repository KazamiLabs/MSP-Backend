<?php

namespace App\Http\Controllers\Admin;

use App\BangumiSetting;
use App\BangumiSettingTag;
use App\Http\Controllers\Controller;
use App\SysSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;

class SettingController extends Controller
{
    //

    public function bangumiSettings(Request $request)
    {
        $limit    = $request->get('limit', 15);
        $settings = BangumiSetting::select('id', 'sitename', 'sitedriver', 'username', 'status')
            ->paginate($limit);
        return $settings;
    }

    public function createBangumiSettings(Request $request)
    {
        $request->validate([
            'sitedriver' => 'required',
            'username'   => 'required',
            'password'   => 'required',
            'status'     => 'required',
            'tags'       => 'array',
        ]);

        $setting = new BangumiSetting($request->all());
        $setting->save();

        // 标签处理
        $tags = Collection::make($request->post('tags'))
            ->map(function ($tagName) {
                $tag = BangumiSettingTag::where('name', $tagName)->first();
                if (!$tag) {
                    $tag = new BangumiSettingTag(['name' => $tagName]);
                    $tag->save();
                }
                return $tag;
            });

        $setting->tags()->saveMany($tags);

        return response([], 201);
    }

    public function updateBangumiSettings(Request $request, $id)
    {
        $setting = BangumiSetting::findOrFail($id);
        $request->validate([
            'sitedriver' => 'required',
            'username'   => 'required',
            'status'     => 'required',
            'tags'       => 'array',
        ]);

        $setting->fill($request->all());
        $setting->save();

        // 标签处理
        $tags = Collection::make($request->post('tags'))
            ->map(function ($tagName) {
                $tag = BangumiSettingTag::where('name', $tagName)->first();
                if (!$tag) {
                    $tag = new BangumiSettingTag(['name' => $tagName]);
                    $tag->save();
                }
                return $tag;
            });

        $setting->tags()->detach();
        $setting->tags()->saveMany($tags);

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

    public function deleteBangumiSettings($id)
    {
        $setting = BangumiSetting::findOrFail($id);
        $setting->tags()->detach();
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

    public function sysSettings()
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

    public function allTags()
    {
        return BangumiSettingTag::pluck('name');
    }

    public function echoSetting()
    {
        return [
            'broadcaster' => 'pusher',
            'key'         => Config::get('broadcasting.connections.pusher.key'),
            'cluster'     => Config::get('broadcasting.connections.pusher.options.cluster'),
            'forceTLS'    => true,
        ];
    }
}
