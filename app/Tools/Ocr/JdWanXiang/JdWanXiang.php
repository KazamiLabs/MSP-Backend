<?php
namespace App\Tools\Ocr\JdWanXiang;

use Exception;
use stdClass;

abstract class JdWanXiang
{
    const API_ADDRESS       = 'https://way.jd.com/';
    const OPERATION_SUCCESS = 10000;

    protected $params = [];

    public function __construct(string $appkey)
    {
        $this->params['appkey'] = $appkey;
    }

    final protected function request(string $body): stdClass
    {
        $ch    = curl_init();
        $class = get_class($this);
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
            $class::API_ADDRESS . '?' . http_build_query($this->params)
        );
        //设置本机的post请求超时时间，如果timeout参数设置60 这里至少设置65
        curl_setopt($ch, CURLOPT_TIMEOUT, 65);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        $result = curl_exec($ch);
        curl_close($ch);

        if ($result === false) {
            throw new Exception('Request failed');
        }

        $data = json_decode($result);
        if ($data === false) {
            throw new Exception('NON-JSON data response');
        }

        if (intval($data->code) !== self::OPERATION_SUCCESS) {
            throw new Exception("接口调用失败: {$data->msg}。");
        }

        return $data;
    }

}
