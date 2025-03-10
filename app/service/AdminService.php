        return $result;
    }
    
    /**
     * 获取存储空间使用情况
     * @return array 空间使用情况
     */
    public function getDiskUsage()
    {
        $config = config('plugin.oos.app.storage');
        $diskConfig = $config['disks'][$config['default']];
        $rootDir = $diskConfig['root'];
        
        // 获取磁盘总空间和可用空间
        $totalSpace = disk_total_space($rootDir);
        $freeSpace = disk_free_space($rootDir);
        $usedSpace = $totalSpace - $freeSpace;
        
        // 获取OOS系统占用的空间
        $oosSpace = $this->getDirSize($rootDir);
        
        // 计算百分比
        $usedPercent = round($usedSpace / $totalSpace * 100, 2);
        $oosPercent = round($oosSpace / $totalSpace * 100, 2);
        
        return [
            'total_space' => $totalSpace,
            'used_space' => $usedSpace,
            'free_space' => $freeSpace,
            'oos_space' => $oosSpace,
            'used_percent' => $usedPercent,
            'oos_percent' => $oosPercent
        ];
    }
    
    /**
     * 获取目录大小
     * @param string $path 路径
     * @return int 大小(字节)
     */
    protected function getDirSize($path)
    {
        $size = 0;
        $files = scandir($path);
        
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            
            $filePath = $path . '/' . $file;
            
            if (is_dir($filePath)) {
                $size += $this->getDirSize($filePath);
            } else {
                $size += filesize($filePath);
            }
        }
        
        return $size;
    }
}