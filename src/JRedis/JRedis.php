<?php

namespace A\JRedis;

/**
 * redis 控制类
 * Class Jredis
 * @package A
 */
class JRedis extends Cache_Redis
{
    public function __construct()
    {
        $this->_openCacheConn();
    }

    public function returnObj()
    {
        return self::$instance;
    }

    public function get($cache_key)
    {
        if (self::$instance->exists($cache_key)) {
            return self::$instance->get($cache_key);
        } else {
            return false;
        }
    }

    public function set($key, $value, $expireResolution = null, $expireTTL = null, $flag = null)
    {
        return self::$instance->set($key, $value, $expireResolution, $expireTTL, $flag);
    }


    public function sAdd($cache_key, array $cache_array)
    {
        return self::$instance->sAdd($cache_key, $cache_array);
    }

    public function smembers($cache_key)
    {
        return self::$instance->smembers($cache_key);
    }

    public function sismember($key, $member)
    {
        return self::$instance->sismember($key, $member);
    }

    public function lindex($key, $index)
    {
        return self::$instance->lindex($key, $index);
    }

    public function lpop($key)
    {
        return self::$instance->lpop($key);
    }

    public function llen($key)
    {
        return self::$instance->llen($key);
    }

    public function rpush($key, array $values)
    {
        return self::$instance->lpop($key, $values);
    }

    public function linsert($key, $whence, $pivot, $value)
    {
        return self::$instance->linsert($key, $whence, $pivot, $value);
    }


    public function __get($key)
    {
        return $this->$key ?? config('jdzx.JRedis.' . $key);
    }

    public function __set($key, $value)
    {
        $this->$key = $value;
    }
}