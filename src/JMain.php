<?php

namespace A;

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

    public function __construct($plat)
    {
        $platNameSpace = '\\A\\' . $plat;
        if (class_exists($platNameSpace)) {
            if ($this->platObj === null) {
                $platClass     = new \ReflectionClass($platNameSpace);
                $this->platObj = $platClass->newInstance();
            }
            $this->initPlatAttribute($plat);
        }
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


    /**
     * 获取实例
     * @return object|null
     */
    public function objResult(): ?object
    {
        return $this->platObj->returnObj();
    }
}