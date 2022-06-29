<?php


namespace jdzx\Upload\Tool;


use jdzx\Upload\Client;

class FormUploader
{

    /**
     * 上传二进制流到, 内部使用
     *
     * @param string $upToken 上传凭证
     * @param string $data 上传二进制流
     * @param string $params 自定义变量，规格参考
     * @param string $mime 上传数据的mimeType
     *
     * @param string $fname
     *
     * @return array    包含已上传文件的信息，类似：
     *                                              [
     *                                                  "hash" => "<Hash string>",
     *                                              ]
     */
    public  function put(
        $up_host,
        $upToken,
        $data,
        $params,
        $mime,
        $fname
    ) {


        //enable crc32 check by default
        $fields['crc32'] = Tool::crc32_data($data);

        if ($params) {
            foreach ($params as $k => $v) {
                $fields[$k] = $v;
            }
        }

        $upHost = $up_host. 'v2/Storage/upLoadFMin';
        $response = Client::multipartPost($upToken, $upHost, $fields, 'files', $fname, $data, $mime);
        if (!$response->ok()) {
            return array(null, new Error($upHost, $response));
        }
        return $response->json();
    }
}