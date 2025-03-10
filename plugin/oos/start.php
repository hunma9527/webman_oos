<?php

use plugin\oos\app\Init;
use plugin\oos\app\FileUtil;

// 在worker启动前初始化
Init::init();

// 将示例文件复制到公共目录
if (is_dir(public_path()) && config('plugin.oos.app.enable', true)) {
    FileUtil::copyExamplesToPublic();
}

// 打印安装信息
echo "OOS 对象存储系统已启动。\n";
echo "管理令牌: " . config('plugin.oos.app.admin_token', '未设置') . "\n";
echo "示例文件: " . public_path() . "/oos-examples/\n";
echo "示例访问: http://localhost:8787/oos-examples/\n";