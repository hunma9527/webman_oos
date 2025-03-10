    /**
     * 创建上传目录
     * @return void
     */
    protected static function createUploadDir()
    {
        $uploadDir = public_path() . '/uploads';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // 创建.htaccess文件，增强安全性
        $htaccessContent = <<<EOT
<IfModule mod_rewrite.c>
    # 允许直接访问图片、视频等常见媒体文件
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} -f
    RewriteRule \.(jpg|jpeg|png|gif|webp|bmp|svg|mp4|webm|ogg|mp3|wav)$ - [L]
    
    # 其他文件需要通过OOS系统访问控制
    RewriteRule !(\.(jpg|jpeg|png|gif|webp|bmp|svg|mp4|webm|ogg|mp3|wav))$ /oos/preview/%{REQUEST_URI} [L,R=301]
</IfModule>

# 禁止执行脚本
<FilesMatch "\.(php|pl|py|jsp|asp|sh|cgi)$">
    Require all denied
</FilesMatch>

# 禁止列目录
Options -Indexes
EOT;
        
        file_put_contents($uploadDir . '/.htaccess', $htaccessContent);
    }
    
    /**
     * 创建临时目录
     * @return void
     */
    protected static function createTempDir()
    {
        $tempDir = runtime_path() . '/temp';
        $chunkDir = $tempDir . '/chunks';
        
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        if (!is_dir($chunkDir)) {
            mkdir($chunkDir, 0755, true);
        }
        
        // 创建.gitignore防止临时文件被提交
        $gitignore = <<<EOT
*
!.gitignore
EOT;
        
        file_put_contents($tempDir . '/.gitignore', $gitignore);
    }
    
    /**
     * 创建配置
     * @return void
     */
    protected static function createConfig()
    {
        // 生成一个随机的管理员令牌
        $adminToken = md5(uniqid() . time() . rand(10000, 99999));
        
        // 写入管理员令牌配置
        $adminTokenConfig = <<<EOT
<?php
return [
    'admin_token' => '{$adminToken}'
];
EOT;
        
        $configDir = config_path() . '/plugin/oos/app';
        
        if (!is_dir($configDir)) {
            mkdir($configDir, 0755, true);
        }
        
        file_put_contents($configDir . '/admin.php', $adminTokenConfig);
        
        // 创建数据库配置文件
        $phinxConfigFile = __DIR__ . '/database/phinx.php';
        if (!file_exists($phinxConfigFile)) {
            $phinxConfig = <<<EOT
<?php
require_once dirname(__DIR__, 4) . '/vendor/autoload.php';
require_once dirname(__DIR__, 4) . '/support/bootstrap.php';

return [
    'paths' => [
        'migrations' => dirname(__FILE__) . '/migrations',
        'seeds' => dirname(__FILE__) . '/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'production',
        'production' => [
            'adapter' => 'mysql',
            'host' => config('database.connections.mysql.host'),
            'name' => config('database.connections.mysql.database'),
            'user' => config('database.connections.mysql.username'),
            'pass' => config('database.connections.mysql.password'),
            'port' => config('database.connections.mysql.port'),
            'charset' => config('database.connections.mysql.charset'),
        ]
    ]
];
EOT;
            
            $databaseDir = __DIR__ . '/database';
            if (!is_dir($databaseDir)) {
                mkdir($databaseDir, 0755, true);
            }
            
            file_put_contents($phinxConfigFile, $phinxConfig);
        }
        
        // 创建SQL导入文件
        $sqlContent = <<<EOT
-- OOS附件系统数据库结构

-- 创建文件表
CREATE TABLE IF NOT EXISTS `oos_files` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `file_hash` char(32) NOT NULL COMMENT '文件MD5哈希',
    `original_name` varchar(255) NOT NULL COMMENT '原始文件名',
    `storage_name` varchar(255) NOT NULL COMMENT '存储文件名',
    `storage_path` varchar(255) NOT NULL COMMENT '存储路径',
    `file_ext` varchar(10) NOT NULL COMMENT '文件扩展名',
    `file_size` bigint(20) UNSIGNED NOT NULL COMMENT '文件大小(字节)',
    `file_type` varchar(100) NOT NULL COMMENT '文件MIME类型',
    `image_width` int(11) DEFAULT NULL COMMENT '图片宽度',
    `image_height` int(11) DEFAULT NULL COMMENT '图片高度',
    `upload_time` datetime NOT NULL COMMENT '上传时间',
    `upload_user_id` int(11) DEFAULT NULL COMMENT '上传用户ID',
    `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态:1正常,0已删除',
    `access_count` int(11) NOT NULL DEFAULT '0' COMMENT '访问次数',
    `last_access_time` datetime DEFAULT NULL COMMENT '最后访问时间',
    `extra` json DEFAULT NULL COMMENT '额外信息',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_file_hash` (`file_hash`),
    KEY `idx_upload_time` (`upload_time`),
    KEY `idx_upload_user` (`upload_user_id`),
    KEY `idx_storage_path` (`storage_path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文件存储表';

-- 创建文件关联表
CREATE TABLE IF NOT EXISTS `oos_file_relations` (
    `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    `file_id` bigint(20) UNSIGNED NOT NULL COMMENT '文件ID',
    `business_type` varchar(50) NOT NULL COMMENT '业务类型',
    `business_id` varchar(64) NOT NULL COMMENT '业务ID',
    `create_time` datetime NOT NULL COMMENT '创建时间',
    `extra` json DEFAULT NULL COMMENT '额外信息',
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_business` (`business_type`,`business_id`,`file_id`),
    KEY `idx_file_id` (`file_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='文件关联表';
EOT;
        
        file_put_contents(__DIR__ . '/database/oos.sql', $sqlContent);
    }
}