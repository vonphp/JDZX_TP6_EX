<?php

namespace A\FastDfs;


use A\Common\common;

class FastDfs
{
    private static $instance;

    public function __construct()
    {
        if (is_null(self::$instance) || !self::$instance instanceof FastDfs) {
            self::$instance = new self();
        }
    }

    public function returnObj(): FastDfs
    {
        return self::$instance;
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

        return common::request('POST', $url, $param . 6);
    }


    public function __get($key)
    {
        return $this->$key;
    }

    public function __set($key, $value)
    {
        $this->$key = $value;
    }

}