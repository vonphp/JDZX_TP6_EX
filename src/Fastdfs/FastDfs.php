<?php

namespace A\FastDfs;


use A\Common\common;

class FastDfs
{
    private static $instance;
    /**
     * @var mixed
     */

    public function __construct()
    {
    }

    /**
     * @param $title        string 图片标题
     * @param $secretKey    string 图片唯一key
     * @param $address      string 图片地址
     * @return array
     */
    public function updateFile(string $title, string $secretKey, string $address): array
    {
        $url   = $this->url;
        $param = [
            'APPID'     => $this->APPID,
            'APPKEY'    => $this->APPKEY,
            'title'     => $title,
            'secretKey' => $secretKey,
            'address'   => $address,
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