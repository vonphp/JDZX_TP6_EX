# JDZX_TP6_EX
版本1

此扩展为胶东在线技术部扩展
### 包含功能：
 1. redis基础操作 + 分布式锁
 2. fastDfs请求
 3. apiSign 接口验证

...
### 使用手册：
#### 1. 在config目录添加 Jdzx.php 配置文件，代码如下：
```phpt
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
```

#### 2. 在控制器中调用代码说明：
```phpt
<?php

namespace app\controller;

use JDzx\JMain;
use app\BaseController;
use think\facade\Queue;

class Index extends BaseController
{
    public function index()
    {
        echo "<pre>";
        // 1. redis使用
        // 1.1 调用类名固定的
        $platClass = 'JRedis'; 
        // 1.2 实例化redis
        $JRedisMain = (new JMain($platClass)); 
        // 1.3 调用方法并传参数
        // run方法 参数1（lock）：调用redis的方法
        // run方法 参数2 []: lock方法的参数，需要数组类型
        $lock = $JRedisMain->run('lock', ['1231','333', 11000]);
        $unlock = $JRedisMain->run('unlock', ['1231','333']);
        $get = $JRedisMain->run('get', ['333']);
        $set = $JRedisMain->run('set', ['333', '123']);

        var_dump($get);
        var_dump($set);
        var_dump($lock);
        var_dump($unlock);
        
        // ApiSign .....
        $platClass  = 'ApiSign';
        $ApiSignmain = (new JMain($platClass));
        $param = [
            'p1' => 'v1',
            'p2' => 'v2'
        ];
        $nonce = 'aaa';
        $timeStamp = time();
        $sign = $ApiSignmain->run('getSign', [$param, $nonce, $timeStamp]);
        var_dump($sign);
        $checkParam = $param;
        $checkParam['sign']      = $sign;
        $checkParam['timestamp'] = $timeStamp;
        $checkParam['nonce']     = $nonce;
        $getSign = $ApiSignmain->run('checkSign', [$checkParam]);
        var_dump($getSign);
        
        // FastDfs .....
        $platClass  = 'FastDfs';
        $ApiSignmain = (new JMain($platClass))->run('upload',['http://www.baidu.com']);
        var_dump($ApiSignmain);
    }
}

```

#### 3. 类与参数说明
##### 3.1 JRedis
```html
加锁 lock($key, $token, $exTime) 请求key，请求id，过期时间（毫秒）
解锁 unlock(string $lock_key, string $token) 请求key，请求id
其他请看方法源码
```

##### 3.2 FastDfs
```html
/**
* @param $title        string 图片标题
* @param $secretKey    string 图片唯一key
* @param $address      string 图片地址
* @return array
*/
上传图片到fastdfs updateFile(string $title, string $secretKey, string $address): array
```

##### 3.4 ApiSign
```html
    /**
     * 校验签名
     * @param array $param 参与签名的参数数组，其中key为参数名 value为参数值，
     * @param string method 请求方式
     * @return bool
     * 校验签名
     */
    public function checkSign(array $param, $method = 'GET')
    
```


