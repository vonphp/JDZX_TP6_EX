<?php

namespace JDzx\FastDfs;

use think\Exception;

/**
 * Class JiaodongDfs
 * @package jiaodong
 * 胶东在线 fastdfs通讯类
 */
class FastDfs
{
    //fastdfs系统实例
    private $fastdfsServer;

    //tracker对象
    private $tracker;

    //storage对象
    private $storage;

    //文件内容
    public $fileInfo = [];

    /**
     * JiaodongDfs constructor.
     * 构造方法
     * @throws \Exception
     */
    public function __construct()
    {
        //获取tracker
        if (!function_exists('fastdfs_tracker_get_connection')) {
            throw  new Exception('Invalid FastDfs extension');
        }

        $this->tracker = fastdfs_tracker_get_connection();
//            dump($this->tracker);
        //获取storage实例
        $this->storage = fastdfs_tracker_query_storage_store();
//            dump($this->storage);
        //获取server实例
        $this->fastdfsServer = fastdfs_connect_server($this->storage['ip_addr'], $this->storage['port']);
    }

    /**
     * @param $localFile
     * @return false
     * 本地文件上传
     * @throws Exception
     */
    public function upload($localFile)
    {
        if (!file_exists($localFile)) {
            throw  new Exception('Invalid Local File');
        }
        $this->fileInfo = fastdfs_storage_upload_by_filename($localFile, null, array(), null, $this->tracker, $this->storage);
        if (!$this->fileInfo) {
            throw  new Exception(fastdfs_get_last_error_info());
        }
        return true;
    }

    /**
     * 获取上传文件结果
     * @return array
     */
    public function getUpFileResult()
    {
        return $this->fileInfo;
    }

    /**
     * 获取远程文件地址
     * @return false|string
     */
    public function getRemoteUrl()
    {
        if ($this->fileInfo) {
            return $this->baseUrl . $this->fileInfo['group_name'] . '/' . $this->fileInfo['filename'];
        }
        return false;
    }

    /**
     * 删除远程文件
     * @param $group
     * @param $file_name
     * @return mixed
     * @throws Exception
     */
    public function delRemoteFile($group, $file_name)
    {
        $result = fastdfs_storage_delete_file($group, $file_name);
        if ($result == 'Success') return true;

        throw  new Exception(fastdfs_get_last_error_info());
    }

    /**
     * 获取远程文件信息
     * @param $group
     * @param $file_name
     * @return mixed
     */
    public function getRemoteFile($group, $file_name)
    {
        return fastdfs_get_file_info($group, $file_name);
    }

    /**
     * 校验远程文件是否存在
     * @param $group
     * @param $file_name
     * @return mixed
     */
    public function checkRemoteFileExist($group, $file_name)
    {
        return fastdfs_storage_file_exist($group, $file_name);
    }
}