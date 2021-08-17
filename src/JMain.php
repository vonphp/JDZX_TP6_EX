<?php

namespace A;

use A\JRedis\JRedis;

/**
 * 胶东在线入口类
 * @create 2021年08月17日09:36:00
 * @author fly
 * Class JMain
 * @package A
 */
class JMain
{
    public $platObj = null;
    public $platClass = null;

    public function __construct($plat)
    {
        $platNameSpace = 'A\\' . $plat . '\\' . $plat;
        if (class_exists($platNameSpace)) {
            if ($this->platObj === null) {
                $this->platClass     = new \ReflectionClass($platNameSpace);
                $this->platObj = $this->platClass->newInstance();
            }
            $this->initPlatAttribute($plat);
        }
    }


    /**
     * 运行请求方法
     * @param String $method
     * @param array $params
     * @throws \ReflectionException
     */
    public function run(String $method, Array $params)
    {
        $reflectionMethod = $this->platClass->getMethod($method);
        if ($reflectionMethod->isStatic()) {
            $items = $reflectionMethod -> invokeArgs(null, $params);
        } else {
            $items = $reflectionMethod -> invokeArgs($this->platObj, $params);
        }
        return $items;
    }


    /**
     * 初始化参数
     * @param $plat
     */
    public function initPlatAttribute($plat)
    {
        foreach (config('Jdzx.' . $plat) as $key => $value) {
            $this->platObj->$key = $value;
        }
    }
}