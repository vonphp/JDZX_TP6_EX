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

    /**
     * 获取key
     * @param $cache_key
     * @return false
     */
    public function get($cache_key): bool
    {
        if (self::$instance->exists($cache_key)) {
            return self::$instance->get($cache_key);
        } else {
            return false;
        }
    }

    /**
     * 设置key
     * @param $key
     * @param $value
     * @param string $expireResolution
     * @param int $expireTTL
     * @param null $flag
     * @return mixed
     */
    public function set($key, $value, string $expireResolution = 'EX', int $expireTTL = 3600, $flag = null)
    {
        return self::$instance->set($key, $value, $expireResolution, $expireTTL);
    }


    /**
     * Redis Sadd 命令将一个或多个成员元素加入到集合中，已经存在于集合的成员元素将被忽略。
     * 假如集合 key 不存在，则创建一个只包含添加的元素作成员的集合。
     * 当集合 key 不是集合类型时，返回一个错误。
     * @param $cache_key
     * @param array $cache_array
     * @return mixed
     */
    public function sAdd($cache_key, array $cache_array)
    {
        return self::$instance->sAdd($cache_key, $cache_array);
    }

    /**
     * Redis Smembers 命令返回集合中的所有的成员。 不存在的集合 key 被视为空集合。
     * @param $cache_key
     * @return mixed
     */
    public function smembers($cache_key)
    {
        return self::$instance->smembers($cache_key);
    }

    /**
     * Redis Sismember 命令判断成员元素是否是集合的成员。
     * @param $key
     * @param $member
     * @return mixed
     */
    public function sismember($key, $member)
    {
        return self::$instance->sismember($key, $member);
    }

    /**
     * Redis Lindex 命令用于通过索引获取列表中的元素。你也可以使用负数下标，
     * 以 -1 表示列表的最后一个元素，
     * -2 表示列表的倒数第二个元素，以此类推。
     * @param $key
     * @param $index
     * @return mixed
     */
    public function lindex($key, $index)
    {
        return self::$instance->lindex($key, $index);
    }

    /**
     * Redis Lpop 命令用于移除并返回列表的第一个元素。
     * @param $key
     * @return mixed
     */
    public function lpop($key)
    {
        return self::$instance->lpop($key);
    }

    /**
     * Redis Llen 命令用于返回列表的长度。 如果列表 key 不存在，则 key 被解释为一个空列表，返回 0 。
     * 如果 key 不是列表类型，返回一个错误。
     * @param $key
     * @return mixed
     */
    public function llen($key)
    {
        return self::$instance->llen($key);
    }

    /**
     * Redis Rpush 命令用于将一个或多个值插入到列表的尾部(最右边)。
     * @param $key
     * @param array $values
     * @return mixed
     */
    public function rpush($key, array $values)
    {
        return self::$instance->lpop($key, $values);
    }

    /**
     * Redis Linsert 命令用于在列表的元素前或者后插入元素。当指定元素不存在于列表中时，不执行任何操作。
     * @param $key
     * @param $whence
     * @param $pivot
     * @param $value
     * @return mixed
     */
    public function linsert($key, $whence, $pivot, $value)
    {
        return self::$instance->linsert($key, $whence, $pivot, $value);
    }


    public function __get($key)
    {
        return $this->$key ?? config('JDzx.JRedis.' . $key);
    }

    public function __set($key, $value)
    {
        $this->$key = $value;
    }
}