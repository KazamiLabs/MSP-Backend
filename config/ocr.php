<?php
/**
 * 验证码 OCR 配置
 */

return [
    'ruokuai'    => [
        'username' => env('RUOKUAI_USERNAME', 'username'),
        'password' => env('RUOKUAI_PASSWORD', 'password'),
    ],

    'jdwanxiang' => [
        'appkey' => env('JD_WANXIANG_APPKEY', ''),
    ],

];
