<?php

namespace app\common\service\Redis;


/**
 * Redis 实现抽象类
 * */
abstract class Cache_RedisCache
{
    protected $_expire;

    protected $_err_code;
    protected $_err_info;

    protected static $_cache_pool;
    protected $_redis;


    public function __construct()
    {
        $this->_err_info = '';
        $this->_err_code = 1; //SUCCESS 1
        $this->_expire   = -1;

        //打开Redis连接
        $this->_openCacheConn();
    }

    /**
     * 设定过期的秒速
     * @param int $seconds
     */
    public function setExpire($seconds)
    {
        $this->_expire = $seconds;
    }

    //打开Redis连接
    protected function _openCacheConn()
    {
        if (empty(self::$_cache_pool)) {
            self::$_cache_pool = (new \Predis\Client([
                'scheme' => env('redis.scheme', ''),
                'host'   => env('redis.host', ''),
                'port'   => env('redis.port', ''),
            ]));;
        }
        self::$_cache_pool->auth(env('redis.auth', ''));
        self::$_cache_pool->select(intval(12));

        $this->_redis = self::$_cache_pool;
    }


    public function get($cache_key)
    {
        if ($this->_redis->exists($cache_key)) {
            $this->cache_value = $this->_redis->get($cache_key);
            return $this->cache_value;
        } else {
            return false;
        }
    }

    public function set($cache_key, $cache_value)
    {
        $this->_redis->set($cache_key, $cache_value);
    }


    public function sAdd($cache_key, $cache_array)
    {
        return $this->_redis->sAdd($cache_key, $cache_array);
    }

    public function smembers($cache_key)
    {
        return $this->_redis->smembers($cache_key);
    }

    public function sismember($key, $member)
    {
        return $this->_redis->sismember($key, $member);
    }

    public function lindex($key, $index)
    {
        return $this->_redis->lindex($key, $index);
    }

    public function lpop($key)
    {
        return $this->_redis->lpop($key);
    }

    public function llen($key)
    {
        return $this->_redis->llen($key);
    }


    public function rpush($key, $values)
    {
        return $this->_redis->lpop($key, $values);
    }

    public function linsert($key, $whence, $pivot, $value)
    {
        return $this->_redis->linsert($key, $whence, $pivot, $value);
    }

    /**
     * 尝试获取锁
     * @param \Predis\Client $redis redis客户端
     * @param String $key 锁
     * @param String $requestId 请求id
     * @param int $exTime 过期时间
     * @return bool                 是否获取成功
     */
    public static function tryGetLock(\Predis\Client $redis, string $key, string $requestId, int $exTime)
    {
        $result = $redis->set($key, $requestId, self::EXPIRE_TIME, $exTime, self::NOT_EXIST);

        return self::LOCK_SUCCESS === (string)$result;
    }

    /**
     * 解锁
     * @param $redis
     * @param $key
     */
    public static function releaseLock(\Predis\Client $redis, string $key, string $requestId)
    {
        $lua = "
        if redis.call('get', KEYS[1]) == ARGV[1] then 
            return redis.call('del', KEYS[1]) 
        else 
            return 0 
        end
        ";

        $result = $redis->eval($lua, 1, $key, $requestId);
        return self::RELEASE_SUCCESS === $result;
    }

}
