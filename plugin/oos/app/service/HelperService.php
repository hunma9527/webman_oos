<?php
namespace plugin\oos\app\service;

class HelperService
{
    /**
     * 获取文件MIME类型
     * @param string $path 文件路径
     * @return string MIME类型
     */
    public static function getMimeType($path)
    {
        // 检查是否有fileinfo扩展
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $path);
            finfo_close($finfo);
            return $mime;
        }
        
        // 备用方法
        $mimeTypes = [
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',
            
            // 图片
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',
            'webp' => 'image/webp',
            
            // 文档
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            
            // 音频
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'ogg' => 'audio/ogg',
            
            // 视频
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            
            // 压缩文件
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            '7z' => 'application/x-7z-compressed',
            'gz' => 'application/gzip',
        ];
        
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        if (array_key_exists($ext, $mimeTypes)) {
            return $mimeTypes[$ext];
        }
        
        return 'application/octet-stream';
    }
    
    /**
     * 检查文件是否为有效的图片
     * @param string $path 文件路径
     * @return bool 是否为图片
     */
    public static function isValidImage($path)
    {
        if (!file_exists($path)) {
            return false;
        }
        
        // 尝试获取图片信息
        $imageInfo = @getimagesize($path);
        
        if ($imageInfo === false) {
            return false;
        }
        
        // 检查是否为支持的图片类型
        $supportedTypes = [
            IMAGETYPE_JPEG,
            IMAGETYPE_PNG,
            IMAGETYPE_GIF,
            IMAGETYPE_BMP,
            IMAGETYPE_WEBP
        ];
        
        return in_array($imageInfo[2], $supportedTypes);
    }
    
    /**
     * 获取随机字符串
     * @param int $length 长度
     * @return string 随机字符串
     */
    public static function getRandomString($length = 16)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $charsLen = strlen($chars);
        $str = '';
        
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[mt_rand(0, $charsLen - 1)];
        }
        
        return $str;
    }
    
    /**
     * 创建目录（如果不存在）
     * @param string $dir 目录路径
     * @param int $mode 权限模式
     * @return bool 是否成功
     */
    public static function makeDir($dir, $mode = 0755)
    {
        if (is_dir($dir)) {
            return true;
        }
        
        $parent = dirname($dir);
        
        if (!is_dir($parent)) {
            self::makeDir($parent, $mode);
        }
        
        return mkdir($dir, $mode);
    }
    
    /**
     * 生成UUID
     * @return string UUID
     */
    public static function generateUuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
    
    /**
     * 检查文件是否安全（非可执行文件）
     * @param string $path 文件路径
     * @param string $filename 文件名
     * @return bool 是否安全
     */
    public static function isFileSafe($path, $filename)
    {
        // 检查扩展名是否为常见危险扩展名
        $dangerousExtensions = [
            'php', 'php3', 'php4', 'php5', 'phtml', 'phar', 'phps',
            'cgi', 'pl', 'py', 'asp', 'aspx', 'jsp', 'sh', 'bash', 'exe',
        ];
        
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $dangerousExtensions)) {
            return false;
        }
        
        // 检查文件前几个字节是否为PHP标签
        $fp = @fopen($path, 'r');
        if ($fp) {
            $content = fread($fp, 100);
            fclose($fp);
            
            if (stripos($content, '<?php') !== false) {
                return false;
            }
        }
        
        return true;
    }
}