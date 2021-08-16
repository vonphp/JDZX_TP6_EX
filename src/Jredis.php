<?php

namespace A;

use A\Redis\Cache_Redis;

/**
 * redis 控制类
 * Class Jredis
 * @package A
 */
class Jredis extends Cache_Redis
{
    public function __construct($exTime)
    {
        $this->config = config('jredis', []);
        $this->exTime = $exTime;

        //打开Redis连接
        $this->_openCacheConn();
    }

    public function get($cache_key)
    {
        if (self::$instance->exists($cache_key)) {
            return self::$instance->get($cache_key);
        } else {
            return false;
        }
    }
}