<?php

namespace App\Tools\Ocr\JdWanXiang;

use App\Tools\Ocr\JdWanXiang\JdWanXiang;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Xunhu extends JdWanXiang
{
    const API_ADDRESS              = 'https://way.jd.com/Xunhu/vercode_check';
    const API_SUCCESS              = 0;
    const API_PARAMS_ERROR         = 1;
    const API_INSUFFICIENT_BALANCE = 2;
    const API_SERVER_ERROR         = 3;

    public function forImageFile(string $filepath)
    {
        $this->params['type']         = 'ne5';
        $this->params['Content-Type'] = 'application/x-www-form-urlencoded';
        if (!is_file($filepath)) {
            throw new Exception('File not exist!');
        }

        $img_base64 = base64_encode(file_get_contents($filepath));

        $sFilepath = Str::after($filepath, Storage::path(''));
        $mime      = Storage::getMimetype($sFilepath);

        $img_base64 = "data:{$mime};base64,{$img_base64}";

        $data = $this->request("img={$img_base64}");

        if (intval($data->result->result) !== self::API_SUCCESS) {
            switch (intval($data->result->result)) {
                case self::API_PARAMS_ERROR:
                    throw new Exception("Xunhu OCR 参数错误: {$data->result->reason}");

                case self::API_INSUFFICIENT_BALANCE:
                    throw new Exception("Xunhu 请充钱: {$data->result->reason}");

                case self::API_SERVER_ERROR:
                    throw new Exception("Xunhu 内部服务器错误: {$data->result->reason}");

                default:
                    throw new Exception("Xunhu 其他错误: {$data->result->reason}");
            }
        }

        return $data->result->code;
    }
}
