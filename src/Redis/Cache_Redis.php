<?php

namespace A\Redis;

use Predis\Client;

/**
 * redis 加锁 --单Redis实例实现分布式锁
 */
abstract class Cache_Redis
{
    const LOCK_SUCCESS = 'OK';
    const NOT_EXIST = 'NX';
    const EXPIRE_TIME = 'PX';
    const RELEASE_SUCCESS = 1;

    public $exTime = 20000;
    static public $instance;
    static public $Predis;

    protected $config = [
        'scheme'   => '',
        'host'     => '',
        'port'     => '',
        'auth'     => '',
        'database' => '',
    ];


    public function __construct()
    {

    }

    //打开Redis连接
    protected function _openCacheConn()
    {
        if (is_null(self::$instance) || !self::$instance instanceof Client) {
            self::$instance = (new Client([
                'scheme' => $this->config['scheme'],
                'host'   => $this->config['host'],
                'port'   => $this->config['port'],
            ]));
        }
        self::$instance->auth($this->config['auth']);
        self::$instance->select(intval($this->config['database']));

        return self::$instance;
    }

    /**
     * 尝试获取锁
     * @return bool                 是否获取成功
     */
    public function lock($key, $token)
    {
        return self::tryGetLock($key, $token, $this->exTime);
    }

    /**
     * 解锁
     * @return bool                 是否获取成功
     */
    public function unlock($lock_key, $token)
    {
        return self::releaseLock($lock_key, $token);
    }

    /**
     * 尝试获取锁
     * @param String $key 锁
     * @param String $requestId 请求id
     * @param int $exTime 过期时间
     * @return bool                 是否获取成功
     */
    public static function tryGetLock(string $key, string $requestId, int $exTime)
    {
        $result = self::$instance->set($key, $requestId, self::EXPIRE_TIME, $exTime, self::NOT_EXIST);

        return self::LOCK_SUCCESS === (string)$result;
    }

    /**
     * 解锁
     * @param $redis
     * @param $key
     */
    public static function releaseLock(string $key, string $requestId)
    {
        $lua = "
        if redis.call('get', KEYS[1]) == ARGV[1] then 
            return redis.call('del', KEYS[1]) 
        else 
            return 0 
        end
        ";

        $result = self::$instance->eval($lua, 1, $key, $requestId);
        return self::RELEASE_SUCCESS === $result;
    }
}

