<?php


namespace jdzx\Upload\Tool;


use jdzx\Upload\Client;

final class ResumeUploader
{

    /**
     * 断点续上传类, 该类主要实现了断点续上传中的分块上传,
     * 以及相应地创建块和创建文件过程.
     *
     */
    private $upToken;
    private $inputStream;
    private $size;
    private $params;
    private $mime;
    private $contexts;
    private $host;
    private $currentUrl;
    private $resumeRecordFile;
    private $partSize;

    /**
     * 上传二进制流到
     *
     * @param string $upToken 上传凭证
     * @param string $inputStream 上传二进制流
     * @param string $size 上传流的大小
     * @param string $params 自定义变量
     * @param string $mime 上传数据的mimeType
     * @param string $resumeRecordFile 断点续传的已上传的部分信息记录文件
     * @param string $version 分片上传版本 目前支持v1/v2版本 默认v1
     * @param string $partSize 分片上传v2字段 默认大小为4MB 分片大小范围为1 MB - 1 GB
     *
     */
    public function __construct(
        $up_host,
        $upToken,
        $inputStream,
        $size,
        $partSize,
        $params,
        $mime,
        $resumeRecordFile = null
    )
    {

        $this->upToken          = $upToken;
        $this->inputStream      = $inputStream;
        $this->size             = $size;
        $this->params           = $params;
        $this->mime             = $mime;
        $this->contexts         = array();
        $this->finishedEtags    = array("etags" => array(), "uploadId" => "", "expiredAt" => 0, "uploaded" => 0);
        $this->resumeRecordFile = $resumeRecordFile ?? null;
        $this->partSize         = $partSize;


        $this->version = 'v1';
        $this->bucket  = 'bucket';
        $this->host    = $up_host;
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
            if ($ret['code'] == 0) {
                return $ret;
            }
            if ($crc != $ret['crc32']) {
                throw new \Exception("file err crc32", 1);
            }
            array_push($this->contexts, $ret['ctx']);

            $uploaded += $blockSize;

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
        $url = $this->host . '/v2/Storage/uploadF/' . $blockSize;
        return $this->post($url, $block);
    }

    private function fileUrl($fname)
    {
        $url = $this->host . '/v2/Storage/mergeFile/fileSize/' . $this->size;
        $url .= '/mimeType/' . Tool::base64_urlSafeEncode($this->mime);

        $url .= '/fname/' . Tool::base64_urlSafeEncode($fname);
        if (!empty($this->params)) {
            foreach ($this->params as $key => $value) {
                $val = Tool::base64_urlSafeEncode($value);
                $url .= "/$key/$val";
            }
        }
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
        return $response->json();
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
}
