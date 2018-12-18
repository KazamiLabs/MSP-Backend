<?php

namespace App\Tools\Time;

class Convert
{
    private $lang;
    private $str;

    public function __construct(string $str)
    {
        $path       = [__DIR__, 'Lang', 'zh.json'];
        $this->lang = json_decode(file_get_contents(implode(DIRECTORY_SEPARATOR, $path)), true);
        $this->str  = $str;
    }

    public function getTimestamp()
    {
        $time_str_arr = [];

        foreach ($this->lang as $key => $f_str) {
            if (preg_match("/{$key}/u", $this->time_str, $matches)) {
                array_shift($matches);
                $time_str_arr[] = vsprintf($f_str, $matches);
            }
        }

        $time_str = implode(' ', $time_str_arr);
        return strtotime($time_str);
    }
}
