<?php

namespace App\Tools\Ocr;

use CURLFile;
use Exception;

class JdWanXiang
{
    const JINGDONG_WANXIANG = 'https://way.jd.com/showapi/checkcode_ys';
    const OPERATION_SUCCESS = 10000;
    const API_SUCCESS       = 0;

    private $params = [];

    public function __construct(string $appkey)
    {
        $this->params = [
            'typeId'         => 35,
            'convert_to_jpg' => 0,
            'appkey'         => $appkey,
        ];
    }

    public function forImageFile(string $filepath)
    {
        if (!is_file($filepath)) {
            throw new Exception('File not exist!');
        }
        // $img_base64 = urlencode(base64_encode(file_get_contents($filepath)));
        $img_base64 = base64_encode(file_get_contents($filepath));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: text/plain',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch,
            CURLOPT_URL,
            self::JINGDONG_WANXIANG . '?' . http_build_query($this->params)
        );
        //设置本机的post请求超时时间，如果timeout参数设置60 这里至少设置65
        curl_setopt($ch, CURLOPT_TIMEOUT, 65);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "img_base64={$img_base64}");
        $result = curl_exec($ch);
        curl_close($ch);

        if ($result === false) {
            throw new Exception('Request failed');
        }

        $data = json_decode($result);
        if ($data === false) {
            throw new Exception('NON-JSON data response');
        }

        dump($data);

        if (intval($data->code) !== self::OPERATION_SUCCESS) {
            throw new Exception("接口调用失败: {$data->msg}。");
        }

        if ($data->result->showapi_res_code !== self::API_SUCCESS) {
            throw new Exception("系统识别失败: {$data->result->showapi_res_error}");
        }

        return $data->result->showapi_res_body->Result;
    }
}
