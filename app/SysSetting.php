<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SysSetting extends Model
{
    //

    const TYPE_STRING   = 1;
    const TYPE_INTEGER  = 2;
    const TYPE_IMAGE    = 3;
    const TYPE_RICHTEXT = 4;

    public static function getValue(string $key)
    {
        $setting = self::where('key', $key)->first();
        if (!$setting) {
            return null;
        }
        $value = self::dealValue($setting);
        return $value;
    }

    public static function getValues(array $key = null)
    {
        if (is_null($key)) {
            $settings = self::get();
        } else {
            $settings = self::whereIn('key', $key)->get();
        }
        if ($settings) {
            $data = $settings
                ->flatMap(function ($setting) {
                    $value = self::dealValue($setting);
                    return [$setting->key => $value];
                });
        } else {
            $data = null;
        }
        return $data;
    }

    public static function setValue(
        string $key,
        string $value,
        int $type = null,
        string $name = null
    ) {
        $setting = self::where('key', $key)->first();
        if (!$setting) {
            $setting       = new self;
            $setting->key  = $key;
            $setting->name = $name;
        }
        $setting->value = $value;
        $setting->type  = is_null($type) ? self::TYPE_STRING : $type;
        if (!is_null($name)) {
            $setting->name = $name;
        }
        $setting->save();
    }

    public function scopeSearchCondition($query, string $keyword = null)
    {
        if (is_null($keyword)) {
            return $query;
        } else {
            return $query
                ->where("key", "like", "%{$keyword}%")
                ->orWhere("name", "like", "%{$keyword}%");
        }
    }

    private static function dealValue(self $setting)
    {
        switch ($setting->type) {
            case self::TYPE_INTEGER:
                $value = intval($setting->value);
                break;
            default:
                $value = $setting->value;
        }
        return $value;
    }
}
