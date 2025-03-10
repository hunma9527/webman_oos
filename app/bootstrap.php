<?php
namespace plugin\oos\app;

use Webman\Bootstrap;
use Workerman\Worker;
use Webman\Config;

class Bootstrap implements Bootstrap
{
    /**
     * onWorkerStart
     * @param Worker $worker
     * @return void
     */
    public static function start($worker)
    {
        // 创建必要的目录
        self::ensureDirectories();
        
        // 设置定时任务
        if ($worker && $worker->id === 0) {
            self::setupCronJobs();
        }
    }
    
    /**
     * 确保必要的目录存在
     * @return void
     */
    protected static function ensureDirectories()
    {
        $uploadDir = public_path() . '/uploads';
        $tempDir = runtime_path() . '/temp';
        $chunkDir = $tempDir . '/chunks';
        
        foreach ([$uploadDir, $tempDir, $chunkDir] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }
    
    /**
     * 设置定时任务
     * @return void
     */
    protected static function setupCronJobs()
    {
        // 每天凌晨2点清理过期的临时文件
        \Workerman\Crontab\Crontab::init();
        
        // 清理超过24小时的临时文件
        new \Workerman\Crontab\Crontab('0 2 * * *', function() {
            $tempDir = runtime_path() . '/temp';
            $chunkDir = $tempDir . '/chunks';
            
            // 清理临时文件
            if (is_dir($tempDir)) {
                self::cleanOldFiles($tempDir, 86400); // 24小时
            }
            
            // 清理分片文件
            if (is_dir($chunkDir)) {
                self::cleanOldDirs($chunkDir, 86400); // 24小时
            }
            
            echo "[OOS] " . date('Y-m-d H:i:s') . " 临时文件清理完成\n";
        });
    }
    
    /**
     * 清理超过指定时间的文件
     * @param string $dir
     * @param int $maxAge 最大年龄（秒）
     * @return void
     */
    protected static function cleanOldFiles($dir, $maxAge)
    {
        $now = time();
        $files = scandir($dir);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || $file === '.gitignore') {
                continue;
            }
            
            $path = $dir . '/' . $file;
            
            if (is_file($path)) {
                $modTime = filemtime($path);
                if (($now - $modTime) > $maxAge) {
                    @unlink($path);
                }
            }
        }
    }
    
    /**
     * 清理超过指定时间的目录
     * @param string $dir
     * @param int $maxAge 最大年龄（秒）
     * @return void
     */
    protected static function cleanOldDirs($dir, $maxAge)
    {
        $now = time();
        $files = scandir($dir);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || $file === '.gitignore') {
                continue;
            }
            
            $path = $dir . '/' . $file;
            
            if (is_dir($path)) {
                $modTime = filemtime($path);
                if (($now - $modTime) > $maxAge) {
                    self::deleteDir($path);
                }
            }
        }
    }
    
    /**
     * 递归删除目录
     * @param string $dir
     * @return bool
     */
    protected static function deleteDir($dir)
    {
        $files = scandir($dir);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $path = $dir . '/' . $file;
            
            if (is_dir($path)) {
                self::deleteDir($path);
            } else {
                @unlink($path);
            }
        }
        
        return @rmdir($dir);
    }
}