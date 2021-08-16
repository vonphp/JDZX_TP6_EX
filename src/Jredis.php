<?php

namespace A;

use A\Redis\Cache_Redis;

class Jredis extends Cache_Redis
{
    public function __construct()
    {
        $this->config    = config('jredis', []);
        $this->_err_info = '';
        $this->_err_code = 1; //SUCCESS 1
        $this->_expire   = -1;


        //打开Redis连接
        $this->_openCacheConn();
    }
}