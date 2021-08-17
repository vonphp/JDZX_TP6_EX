<?php

namespace A\FastDfs;


use A\Common\common;

class FastDfs
{
    private static $instance;

    public function __construct()
    {
    }

    public function updateFile($title): array
    {
        $url   = $this->url;
        $param = [
            'APPID'     => $this->APPID,
            'APPKEY'    => $this->APPKEY,
            'title'     => $title,
            'secretKey' => $this->secretKey,
            'address'   => $this->address,
        ];
        return common::request('POST', $url, $param , 6);
    }


    public function __get($key)
    {
        return $this->$key ?? config('jdzx.FastDfs.' . $key);
    }

    public function __set($key, $value)
    {
        $this->$key = $value;
    }

}