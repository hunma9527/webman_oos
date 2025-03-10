<?php
namespace plugin\oos\app;

class Init
{
    /**
     * 初始化插件
     */
    public static function init()
    {
        // 检查必要的目录和文件
        self::ensureDirectories();
        
        // 复制示例文件到公共目录
        FileUtil::copyExamplesToPublic();
        
        // 检查配置文件
        self::ensureConfig();
        
        // 创建默认水印图片
        self::createDefaultWatermark();
    }
    
    /**
     * 确保必要的目录存在
     */
    protected static function ensureDirectories()
    {
        // 上传目录
        $uploadDir = public_path() . '/uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // 临时目录
        $tempDir = runtime_path() . '/temp';
        $chunkDir = $tempDir . '/chunks';
        
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        if (!is_dir($chunkDir)) {
            mkdir($chunkDir, 0755, true);
        }
    }
    
    /**
     * 确保配置文件存在
     */
    protected static function ensureConfig()
    {
        $configDir = config_path() . '/plugin/oos/app';
        $storageConfigFile = $configDir . '/storage.php';
        
        // 如果配置文件不存在，复制默认配置
        if (!is_file($storageConfigFile)) {
            if (!is_dir($configDir)) {
                mkdir($configDir, 0755, true);
            }
            
            // 复制存储配置
            $defaultStorageConfig = dirname(__DIR__) . '/config/app/storage.php';
            if (is_file($defaultStorageConfig)) {
                copy($defaultStorageConfig, $storageConfigFile);
            }
        }
        
        // 如果管理员令牌配置不存在，生成一个随机令牌
        $adminConfigFile = $configDir . '/admin.php';
        if (!is_file($adminConfigFile)) {
            // 生成随机令牌
            $adminToken = md5(uniqid() . time() . rand(10000, 99999));
            
            $adminConfig = <<<EOT
<?php
return [
    'admin_token' => '{$adminToken}'
];
EOT;
            file_put_contents($adminConfigFile, $adminConfig);
        }
    }
    
    /**
     * 创建默认水印图片
     */
    protected static function createDefaultWatermark()
    {
        $watermarkFile = public_path() . '/watermark.png';
        
        // 如果水印文件不存在，创建一个默认的
        if (!is_file($watermarkFile)) {
            // 创建一个简单的半透明水印
            $width = 200;
            $height = 50;
            $image = imagecreatetruecolor($width, $height);
            
            // 设置透明背景
            imagesavealpha($image, true);
            $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
            imagefill($image, 0, 0, $transparent);
            
            // 添加文字
            $textColor = imagecolorallocatealpha($image, 255, 255, 255, 40); // 白色，半透明
            $text = 'OOS Watermark';
            $fontSize = 5;
            $fontWidth = imagefontwidth($fontSize) * strlen($text);
            $fontHeight = imagefontheight($fontSize);
            $x = ($width - $fontWidth) / 2;
            $y = ($height - $fontHeight) / 2;
            
            imagestring($image, $fontSize, $x, $y, $text, $textColor);
            
            // 保存图片
            imagepng($image, $watermarkFile);
            imagedestroy($image);
        }
    }
}