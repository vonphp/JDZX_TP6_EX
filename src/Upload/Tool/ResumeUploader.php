<?php


namespace jdzx\Upload\Tool;


use jdzx\Upload\Client;
use jdzx\Upload\Config;

final class ResumeUploader
{

    /**
     * 断点续上传类, 该类主要实现了断点续上传中的分块上传,
     * 以及相应地创建块和创建文件过程.
     *
     * @link http://developer.qiniu.com/docs/v6/api/reference/up/mkblk.html
     * @link http://developer.qiniu.com/docs/v6/api/reference/up/mkfile.html
     */
    private $upToken;
    private $key;
    private $inputStream;
    private $size;
    private $params;
    private $mime;
    private $contexts;
    private $finishedEtags;
    private $host;
    private $bucket;
    private $currentUrl;
    private $config;
    private $resumeRecordFile;
    private $version;
    private $partSize;

    /**
     * 上传二进制流到七牛
     *
     * @param string $upToken 上传凭证
     * @param string $key 上传文件名
     * @param string $inputStream 上传二进制流
     * @param string $size 上传流的大小
     * @param string $params 自定义变量
     * @param string $mime 上传数据的mimeType
     * @param Config $config
     * @param string $resumeRecordFile 断点续传的已上传的部分信息记录文件
     * @param string $version 分片上传版本 目前支持v1/v2版本 默认v1
     * @param string $partSize 分片上传v2字段 默认大小为4MB 分片大小范围为1 MB - 1 GB
     *
     * @link http://developer.qiniu.com/docs/v6/api/overview/up/response/vars.html#xvar
     */
    public function __construct(
        $upToken,
        $key,
        $inputStream,
        $size,
        $params,
        $mime,
        $config,
        $resumeRecordFile = null,
        $version = 'v2',
    )
    {

        $this->upToken          = $upToken;
        $this->key              = $key;
        $this->inputStream      = $inputStream;
        $this->size             = $size;
        $this->params           = $params;
        $this->mime             = $mime;
        $this->contexts         = array();
        $this->finishedEtags    = array("etags" => array(), "uploadId" => "", "expiredAt" => 0, "uploaded" => 0);
        $this->config           = $config;
        $this->resumeRecordFile = $resumeRecordFile ?? null;
        $this->partSize         = $this->block_size;


        $this->version = 'v1';
        $this->bucket  = 'bucket';
        $this->host    = $this->up_host;
    }


    /**
     * 上传操作
     */
    public function upload($fname)
    {
        $uploaded = 0;

        // get upload record from resumeRecordFile
        $blkputRets = null;
        if (file_exists($this->resumeRecordFile)) {
            $stream = fopen($this->resumeRecordFile, 'r');
            if ($stream) {
                $streamLen = filesize($this->resumeRecordFile);
                if ($streamLen > 0) {
                    $contents = fread($stream, $streamLen);
                    fclose($stream);
                    if ($contents) {
                        $blkputRets = json_decode($contents, true);
                        if ($blkputRets === null) {
                            error_log("resumeFile contents decode error");
                        }
                    } else {
                        error_log("read resumeFile failed");
                    }
                } else {
                    error_log("resumeFile is empty");
                }
            } else {
                error_log("resumeFile open failed");
            }
        } else {
            error_log("resumeFile not exists");
        }

        if ($blkputRets) {
            if (isset($blkputRets['contexts']) && isset($blkputRets['uploaded']) &&
                is_array($blkputRets['contexts']) && is_int($blkputRets['uploaded'])) {
                $this->contexts = $blkputRets['contexts'];
                $uploaded       = $blkputRets['uploaded'];
            }

        }

        while ($uploaded < $this->size) {
            $blockSize = $this->blockSize($uploaded);
            var_dump('第一次blocsize');
            var_dump($blockSize);
            $data = fread($this->inputStream, $blockSize);
            if ($data === false) {
                throw new \Exception("file read failed", 1);
            }

            $crc      = Tool::crc32_data($data);
            $response = $this->makeBlock($data, $blockSize);
            $ret      = null;
            if ($response->ok() && $response->json() != null) {
                $ret = $response->json();
            }
            array_push($this->contexts, $ret['ctx']);

            $uploaded += $blockSize;
            var_dump($ret);

            $recordData = array(
                'contexts' => $this->contexts,
                'uploaded' => $uploaded
            );
            $recordData = json_encode($recordData);

            if ($recordData) {
                $isWritten = file_put_contents($this->resumeRecordFile, $recordData);
                if ($isWritten === false) {
                    error_log("write resumeRecordFile failed");
                }
            } else {
                error_log('resumeRecordData encode failed');
            }
        }

        return $this->makeFile($fname);
    }

    /**
     * 创建块
     */
    private function makeBlock($block, $blockSize)
    {
        $url = $this->host . '/Storage/uploadF/' . $blockSize;
        return $this->post($url, $block);
    }

    private function fileUrl($fname)
    {
        $url = $this->host . '/Storage/mergeFile/fileSize/' . $this->size;
        $url .= '/mimeType/' . Tool::base64_urlSafeEncode($this->mime);
        if ($this->key != null) {
            $url .= '/key/' . Tool::base64_urlSafeEncode($this->key);
        }
        $url .= '/fname/' . Tool::base64_urlSafeEncode($fname);
        if (!empty($this->params)) {
            foreach ($this->params as $key => $value) {
                $val = Tool::base64_urlSafeEncode($value);
                $url .= "/$key/$val";
            }
        }

        var_dump($url);
        return $url;
    }

    /**
     * 创建文件
     */
    private function makeFile($fname)
    {
        $url      = $this->fileUrl($fname);
        $body     = implode(',', $this->contexts);
        $response = $this->post($url, $body);
        if ($response->needRetry()) {
            $response = $this->post($url, $body);
        }
        if (!$response->ok()) {
            return array(null, new Error($this->currentUrl, $response));
        }
        return $response;
    }

    private function post($url, $data)
    {
        $this->currentUrl = $url;
        $headers          = array('Authorization' => $this->upToken);
        return Client::post($url, $data, $headers);
    }

    private function blockSize($uploaded)
    {
        if ($this->size < $uploaded + $this->partSize) {
            return $this->size - $uploaded;
        }
        return $this->partSize;
    }

    private function makeInitReq($encodedObjectName)
    {
        $res                              = $this->initReq($encodedObjectName);
        $this->finishedEtags["uploadId"]  = $res['uploadId'];
        $this->finishedEtags["expiredAt"] = $res['expireAt'];
    }

    /**
     * 初始化上传任务
     */
    private function initReq($encodedObjectName)
    {
        $url      = $this->host . '/buckets/' . $this->bucket . '/objects/' . $encodedObjectName . '/uploads';
        $headers  = array(
            'Authorization' => 'UpToken ' . $this->upToken,
            'Content-Type'  => 'application/json'
        );
        $response = $this->postWithHeaders($url, null, $headers);
        return $response->json();
    }

    /**
     * 分块上传v2
     */
    private function uploadPart($block, $partNumber, $uploadId, $encodedObjectName, $md5)
    {
        var_dump($block);
        $headers  = array(
            'Authorization' => 'UpToken ' . $this->upToken,
            'Content-Type'  => 'application/octet-stream',
            'Content-MD5'   => $md5
        );
        $url      = $this->host . '/buckets/' . $this->bucket . '/objects/' . $encodedObjectName .
            '/uploads/' . $uploadId . '/' . $partNumber;
        $response = $this->put($url, $block, $headers);
        return $response;
    }

    private function completeParts($fname, $uploadId, $encodedObjectName)
    {
        $headers     = array(
            'Authorization' => 'UpToken ' . $this->upToken,
            'Content-Type'  => 'application/json'
        );
        $etags       = $this->finishedEtags['etags'];
        $sortedEtags = Tool::arraySort($etags, 'partNumber');
        $metadata    = array();
        $customVars  = array();
        if ($this->params) {
            foreach ($this->params as $k => $v) {
                if (strpos($k, 'x:') === 0) {
                    $customVars[$k] = $v;
                } elseif (strpos($k, 'x-qn-meta-') === 0) {
                    $metadata[$k] = $v;
                }
            }
        }
        if (empty($metadata)) {
            $metadata = null;
        }
        if (empty($customVars)) {
            $customVars = null;
        }
        $body     = array(
            'fname'      => $fname,
            'mimeType'   => $this->mime,
            'metadata'   => $metadata,
            'customVars' => $customVars,
            'parts'      => $sortedEtags
        );
        $jsonBody = json_encode($body);
        $url      = $this->host . '/buckets/' . $this->bucket . '/objects/' . $encodedObjectName . '/uploads/' . $uploadId;
        $response = $this->postWithHeaders($url, $jsonBody, $headers);
        if ($response->needRetry()) {
            $response = $this->postWithHeaders($url, $jsonBody, $headers);
        }
        if (!$response->ok()) {
            return array(null, new Error($this->currentUrl, $response));
        }
        return array($response->json(), null);
    }

    private function put($url, $data, $headers)
    {
        $this->currentUrl = $url;
        return Client::put($url, $data, $headers);
    }

    private function postWithHeaders($url, $data, $headers)
    {
        $this->currentUrl = $url;
        return Client::post($url, $data, $headers);
    }
}
