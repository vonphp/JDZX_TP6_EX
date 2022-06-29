<?php


namespace jdzx\Upload;


use jdzx\Upload\Tool\FormUploader;
use jdzx\Upload\Tool\ResumeUploader;

class Upload
{
    private $config;

    public function __construct(Config $config = null)
    {
        if ($config === null) {
            $config = new Config();
        }
        $this->config = $config;
    }

    /**
     * 上传文件到七牛
     *
     * @param string $upToken 上传凭证
     * @param string $filePath 上传文件的路径
     * @param array<string, mixed> $params 定义变量，规格参考
     * @param boolean $mime 上传数据的mimeType
     * @param string $checkCrc 是否校验crc32
     * @param string $resumeRecordFile 断点续传文件路径 默认为null
     * @param string $version 分片上传版本 目前支持v1/v2版本 默认v1
     * @param int $partSize 分片上传v2字段 默认大小为4MB 分片大小范围为1 MB - 1 GB
     *
     * @return array<string, mixed> 包含已上传文件的信息，类似：
     *                                              [
     *                                                  "hash" => "<Hash string>",
     *                                                  "key" => "<Key string>"
     *                                              ]
     * @throws \Exception
     */
    public function putFile(
        $upToken,
        $filePath,
        $params = null,
        $mime = 'application/octet-stream',
        $resumeRecordFile = null,
        $checkCrc = false
    )
    {
        $partSize = $this->block_size;
        if (!file_exists($filePath)) {
            throw new \Exception("file can not file_exists", 1);
        }
        $file = fopen($filePath, 'rb');
        if ($file === false) {
            throw new \Exception("file can not open", 1);
        }
        $stat = fstat($file);
        $size = $stat['size'];
        if ($size <= $partSize) {
            $data = fread($file, $size);
            fclose($file);
            if ($data === false) {
                throw new \Exception("file can not read", 1);
            }
            $fUp = new FormUploader();
            return $fUp->put(
                $this->up_host,
                $upToken,
                $data,
                $params,
                $mime,
                basename($filePath)
            );
        }

        $up  = new ResumeUploader(
            $this->up_host,
            $upToken,
            $file,
            $size,
            $partSize,
            $params,
            $mime,
            $resumeRecordFile
        );
        fclose($file);
        return $up->upload(basename($filePath));
    }

}