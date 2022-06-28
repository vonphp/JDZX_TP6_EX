<?php


namespace jdzx\Jwt;

use Firebase\JWT\Key;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;


/**
 * Class Jwt
 * @package jdzx\Jwt
 * @author: Fly
 * @describe: 单例 一次请求中所有出现jwt的地方都是一个用户
 */
class JwtS
{
    // jwt token
    private $token;
    private $kid = 1;

    /**
     * JwtService constructor.
     * 私有化构造函数
     */
    public function __construct()
    {
    }

    /**
     * @author: Fly
     * @describe:私有化clone函数
     */
    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    /**
     * @return $this
     * @author: Fly
     * @describe:编码jwt token
     */
    public function encode($data, $key)
    {
        $token       = array(
            "iat"  => time(),
            "nbf"  => time() + env('jwt.nbf', 0),
            "exp"  => time() + env('jwt.exp_time', 7200), //token 过期时间
            'data' => $data//可以用户ID，可以自定义
        ); //Payload
        $this->token = JWT::encode($token, $key, env('jwt.alg', 'HS256'), $this->kid); //此处行进加密算法生成jwt
        return $this->token;
    }


    /**
     * @return \Lcobucci\JWT\Token
     * @author: Fly
     * @describe:解码jwt token
     */
    public function decode($decodeToken, $key)
    {

        try {
            if (!$decodeToken) {
                return false;
            }
            JWT::$leeway = 20;//当前时间减去60，把时间留点余地

            $decoded = JWT::decode($decodeToken, new Key($key, 'HS256')); //HS256方式，这里要和签发的时候对应
            return $decoded->data;

        } catch (SignatureInvalidException $e) {  //签名不正确
            return '签名不正确';
        } catch (BeforeValidException $e) {  // 签名在某个时间点之后才能用
            return '签名在某个时间点之后才能用';
        } catch (ExpiredException $e) {  // token过期
            return 'token过期';
        } catch (Exception $e) {  //其他错误
            return '其他错误';
        }
    }
}