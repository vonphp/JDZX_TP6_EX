<?php


namespace jdzx\Upload\Tool;


use jdzx\Upload\Client;
use jdzx\Upload\Config;

class FormUploader
{
    /**
     * 上传二进制流到七牛, 内部使用
     *
     * @param string $upToken 上传凭证
     * @param string $key 上传文件名
     * @param string $data 上传二进制流
     * @param Config $config 上传配置
     * @param string $params 自定义变量，规格参考
     *                    https://developer.qiniu.com/kodo/manual/1235/vars#xvar
     * @param string $mime 上传数据的mimeType
     *
     * @param string $fname
     *
     * @return array    包含已上传文件的信息，类似：
     *                                              [
     *                                                  "hash" => "<Hash string>",
     *                                                  "key" => "<Key string>"
     *                                              ]
     */
    public static function put(
        $upToken,
        $key,
        $data,
        $config,
        $params,
        $mime,
        $fname
    ) {

        if ($key === null) {
        } else {
            $fields['key'] = $key;
        }
        //enable crc32 check by default
        $fields['crc32'] = Tool::crc32_data($data);

        if ($params) {
            foreach ($params as $k => $v) {
                $fields[$k] = $v;
            }
        }

        $upHost = Config::UP_HOST . 'v2/Storage/upLoadFMin';
        $response = Client::multipartPost($upToken, $upHost, $fields, 'files', $fname, $data, $mime);
        if (!$response->ok()) {
            return array(null, new \Error($upHost, $response));
        }
        return array($response->json(), null);
    }
}