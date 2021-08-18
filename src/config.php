<?php
return [
    'JRedis'  => [
        'scheme'   => 'tcp',
        'host'     => '127.0.0.1',
        'port'     => '6379',
        'auth'     => '123123',
        'database' => '1',
    ],
    'ApiSign' => [
        'timeReduce' => 115, // 时间误差，如果超出误差，签名失效
    ],
    'FastDfs' => [
        'url'       => 'http://attatch.c.jiaodong.cn/jd_attatch_serv/public/index.php/api/upload/upload',
        'APPID'     => '1',
        'APPKEY'    => '1234567890',
    ],
];