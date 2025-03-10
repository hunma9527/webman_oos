<?php

return [
    // 默认存储配置
    'default' => 'local',
    
    // 存储位置配置
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => public_path() . '/uploads',
            'url' => '/uploads',
            'permissions' => [
                'file' => [
                    'public' => 0664,
                    'private' => 0600,
                ],
                'dir' => [
                    'public' => 0775,
                    'private' => 0700,
                ],
            ],
        ],
        // 可以添加其他存储配置，如OSS、S3等
    ],
    
    // 文件命名策略: hash, date, uuid
    'naming_strategy' => 'hash',
    
    // 目录结构策略: date, hash, none
    'directory_strategy' => 'date',
    
    // 图片处理配置
    'image' => [
        'driver' => 'gd',  // 可选: gd, imagick
        'thumbnail_sizes' => [
            'small' => [100, 100],
            'medium' => [300, 300],
            'large' => [600, 600],
        ],
        'quality' => 90,
        'watermark' => [
            'enabled' => false,
            'image' => public_path() . '/watermark.png',
            'position' => 'bottom-right', // 可选: top-left, top-right, bottom-left, bottom-right, center
            'opacity' => 50, // 0-100
        ],
    ],
    
    // 缓存配置
    'cache' => [
        'enabled' => true,
        'ttl' => 86400, // 缓存有效期（秒）
    ],
    
    // 允许的文件类型（MIME类型）
    'allowed_mime_types' => [
        // 图片
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
        'image/bmp',
        // 文档
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain',
        'text/html',
        'text/css',
        'text/javascript',
        // 媒体
        'audio/mpeg',
        'audio/wav',
        'audio/ogg',
        'video/mp4',
        'video/webm',
        'video/ogg',
        // 压缩文件
        'application/zip',
        'application/x-rar-compressed',
        'application/x-7z-compressed',
        'application/gzip',
        // 其他
        'application/json',
        'application/xml',
    ],
    
    // 允许的最大文件大小（字节）
    'max_file_size' => 50 * 1024 * 1024, // 50MB
];