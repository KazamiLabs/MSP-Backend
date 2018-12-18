<?php

namespace App\Tools\Ocr;

class Ruokuai
{
    const RUOKUAI_API               = 'http://api.ruokuai.com/create.json';
    const FOUR_HYBRID_WORD_NUM_TYPE = 3050;
    const SOFT_ID                   = '23066';
    const SOFT_KEY                  = 'a574d6c397a943cea48a8874f90dc5cd';

    private $params = [];

    public function __construct(string $username, string $password)
    {
        $this->params = [
            'username' => $username,
            'password' => $password,
            'typeid'   => self::FOUR_HYBRID_WORD_NUM_TYPE,
            'softid'   => self::SOFT_ID,
            'softkey'  => self::SOFT_KEY,
            'submit'   => 'Submit',
        ];
    }

    public function forImageFile(string $filepath)
    {
        if (!is_file($filepath)) {
            throw new \Exception('File not exist!');
        }
        $this->params['image'] = new \CURLFile($filepath);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, self::RUOKUAI_API);
        //设置本机的post请求超时时间，如果timeout参数设置60 这里至少设置65
        curl_setopt($ch, CURLOPT_TIMEOUT, 65);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->params);
        $result = curl_exec($ch);
        curl_close($ch);

        if ($result === false) {
            throw new \Exception('Request failed');
        }

        $data = json_decode($result);
        if ($data === false) {
            throw new \Exception('NON-JSON data response');
        }

        return $data->Result;
    }
}
