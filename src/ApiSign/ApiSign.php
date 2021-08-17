<?php

namespace A\ApiSign;

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


    public function __get($key)
    {
        return $this->$key;
    }
    public function __set($key, $value)
    {
        return $this->$key = $value;
    }

    //时间误差，如果超出误差，则签名失效
    private $timeReduce = 115;

    /**
     * 校验签名
     * @param array $param 参与签名的参数数组，其中key为参数名 value为参数值，
     * @param string method 请求方式
     * @return bool
     * 校验签名
     */
    public function checkSign($param, $method = 'GET')
    {
        $now = time();

        //判断是否存在必要参数
        if (!isset($param['sign']) || !isset($param['timestamp']) || !isset($param['nonce'])) {
            return 11100;
        }

        //判断timestamp是否超时
        if (intval($param['timestamp'] + $this->timeReduce) < $now) {
            return 11200;
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
            return 11300;
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
    public function getSign($param, $nonce, $timeStamp, $method = 'GET')
    {
        $param['timestamp'] = $timeStamp;
        $param['method']    = $method;
        $param['nonce']     = $nonce;

        ksort($param, SORT_STRING);

        $sortedParamString = http_build_query($param);

        return strtoupper(md5($sortedParamString));
    }

    public function test()
    {
        $param     = [
            'p1' => 'v1',
            'p2' => 'v2'
        ];
        $timeStamp = time();
        $nonce     = 'aaa';
        $sign      = $this->getSign($param, $nonce, $timeStamp, 'GET');
        dump($sign);

        $checkParam              = [
            'p1' => 'v1',
            'p2' => 'v2'
        ];
        $checkParam['sign']      = $sign;
        $checkParam['timestamp'] = $timeStamp;
        $checkParam['nonce']     = $nonce;

        dump($this->checkSign($checkParam, 'GET'));
    }
}