<?php

namespace App\Tools\Ocr\JdWanXiang;

use App\Tools\Ocr\JdWanXiang\JdWanXiang;
use Exception;

class Showapi extends JdWanXiang
{
    const API_ADDRESS = 'https://way.jd.com/showapi/checkcode_ys';
    const API_SUCCESS = 0;

    public function forImageFile(string $filepath)
    {
        $this->params['typeId']         = 35;
        $this->params['convert_to_jpg'] = 0;
        if (!is_file($filepath)) {
            throw new Exception('File not exist!');
        }
        $img_base64 = base64_encode(file_get_contents($filepath));

        $data = $this->request("img_base64={$img_base64}");

        if ($data->result->showapi_res_code !== self::API_SUCCESS) {
            throw new Exception("系统识别失败: {$data->result->showapi_res_error}");
        }

        return $data->result->showapi_res_body->Result;
    }
}
