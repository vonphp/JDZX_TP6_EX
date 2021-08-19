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
        'baseUrl' => 'http://uploads.c.jiaodong.cn/',     //服务器基地址
    ],
];