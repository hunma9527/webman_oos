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
    ],
    
    // 文件命名策略
    'naming_strategy' => 'hash', // options: hash, date, uuid
    
    // 目录结构策略
    'directory_strategy' => 'date', // options: date, hash, none
    
    // 图片处理配置
    'image' => [
        'driver' => 'gd', // options: gd, imagick
        'thumbnail_sizes' => [
            'small' => [100, 100],
            'medium' => [300, 300],
            'large' => [600, 600],
        ],
        'quality' => 90,
        'watermark' => [
            'enabled' => false,
            'image' => public_path() . '/watermark.png',
            'position' => 'bottom-right', // top-left, top-right, bottom-left, bottom-right, center
            'opacity' => 50, // 0-100
        ],
    ],
    
    // 缓存配置
    'cache' => [
        'enabled' => true,
        'ttl' => 86400, // 24 hours
    ],
];