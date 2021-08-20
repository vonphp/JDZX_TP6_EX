<?php

namespace JDzx\JdConfig;

class JdConfig
{
    public static function addConfig()
    {
        $sourcefile = str_replace('\\', '/', realpath(dirname(__FILE__) . '/../config.php'));
        $dir        = str_replace('\\', '/', realpath(dirname(__FILE__) . '/../../../../../config/'));
        $filename   = 'JDzx.php';
        self::file2dir($sourcefile, $dir, $filename);
    }

    /**
     * 复制图片
     * @param $sourcefile string 复制文件
     * @param $dir   string 指定文件目录
     * @param $filename string 文件名字
     * @return bool
     */
    static function file2dir(string $sourcefile, string $dir, string $filename): bool
    {
        if (!file_exists($sourcefile)) {
            return false;
        }
        //$filename = basename($sourcefile);
        return copy($sourcefile, $dir . '/' . $filename);
    }
}