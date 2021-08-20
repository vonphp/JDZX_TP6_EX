<?php

namespace TP6\ApiSign;

use think\Exception;

/**
 * Class JiaodongSign
 * @package jiaodong
 * API接口升签算法
 * 请求方生成一个随机字符串
 * 所有参数按参数值升序排列，以=连接参数与值 &连接参数 并拼接当次请求的时间戳参数timestamp(不需要进行强制类型转换，保持inteager类型即可)与请求方式参数method(method的值需大写)与随机字符串nonce
 * 例如：param1=value1&param2=value2&timestamp=12345678&method=GET&nonce=randomString
 * 拼接完成后进行MD5加密并转大写，得到当次请求签名signString
 * 真实请求地址为 接口地址?param1=value1&param2=value2&timestamp=12345678&nonce=randomString&sign=signString
 *
 */

class ApiSign
{

    public function __construct()
    {
    }

    /**
     * 校验签名
     * @param array $param 参与签名的参数数组，其中key为参数名 value为参数值，
     * @param string method 请求方式
     * @return bool
     * 校验签名
     * @throws Exception
     */
    public function checkSign(array $param, $method = 'GET')
    {
        $now = time();

        //判断是否存在必要参数
        if (!isset($param['sign']) || !isset($param['timestamp']) || !isset($param['nonce'])) {
            throw  new Exception('Missing parameters');
        }

        //判断timestamp是否超时
        if (intval($param['timestamp'] + $this->timeReduce) < $now) {
            throw  new Exception('Request timed out');
        }

        //将sign剔除
        $sign = $param['sign'];
        unset($param['sign']);

        //补充Method
        $param['method'] = $method;

        //升序排列
        ksort($param, SORT_STRING);

        $sortedParamString = urldecode(http_build_query($param));
        $thisSign          = strtoupper(md5($sortedParamString));

        if ($thisSign != $sign) {
            throw  new Exception('Signature error');
        }

        return true;
    }

    /**
     * @param array $param 请求业务参数
     * @param string $nonce 随机字符串
     * @param integer $timeStamp 时间戳
     * @param string $method 请求方式
     * @return string
     *
     */
    public function getSign(array $param, string $nonce, int $timeStamp, string $method = 'GET'): string
    {
        $param['timestamp'] = $timeStamp;
        $param['method']    = $method;
        $param['nonce']     = $nonce;

        ksort($param, SORT_STRING);

        $sortedParamString = http_build_query($param);

        return strtoupper(md5($sortedParamString));
    }

    public function __get($key)
    {
        return $this->$key ?? config('JDzx.ApiSign.' . $key);
    }

    public function __set($key, $value)
    {
        $this->$key = $value;
    }
}