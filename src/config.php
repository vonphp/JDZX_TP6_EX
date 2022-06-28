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
    'Upload'  => [
        'sdk_ver'    => '7.6.0',
        'block_size' => 4194304, //4*1024*1024 分块上传块大小，该参数为接口规格，不能修改
        'up_host'    => 'http://attatch.c.jiaodong.cn/jd_attatch_serv/public/index.php/api/',
    ],
    'JwtS' => [],
];