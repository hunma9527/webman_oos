            // 只删除数据库记录，保留物理文件
            $file->status = 0;
            return $file->save();
        }
        
        // 删除物理文件
        $diskConfig = $this->config['disks'][$this->config['default']];
        $filePath = $diskConfig['root'] . '/' . $file->storage_path . '/' . $file->storage_name;
        
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // 删除可能存在的缩略图
        $this->deleteRelatedThumbnails($file);
        
        // 更新数据库记录
        $file->status = 0;
        return $file->save();
    }
    
    /**
     * 删除相关缩略图
     * @param File $file 文件对象
     */
    protected function deleteRelatedThumbnails(File $file)
    {
        if (strpos($file->file_type, 'image/') !== 0) {
            return;
        }
        
        $diskConfig = $this->config['disks'][$this->config['default']];
        $basePath = $diskConfig['root'] . '/' . $file->storage_path;
        $baseFilename = pathinfo($file->storage_name, PATHINFO_FILENAME);
        $extension = pathinfo($file->storage_name, PATHINFO_EXTENSION);
        
        foreach ($this->config['image']['thumbnail_sizes'] as $size => $dimensions) {
            $thumbnailPath = $basePath . '/thumbnails/' . $baseFilename . '_' . $size . '.' . $extension;
            if (file_exists($thumbnailPath)) {
                unlink($thumbnailPath);
            }
        }
    }
    
    /**
     * 获取文件内容
     * @param File $file 文件对象
     * @return string|bool 文件内容或失败时返回false
     */
    public function getContents(File $file)
    {
        $diskConfig = $this->config['disks'][$this->config['default']];
        $filePath = $diskConfig['root'] . '/' . $file->storage_path . '/' . $file->storage_name;
        
        if (!file_exists($filePath)) {
            return false;
        }
        
        return file_get_contents($filePath);
    }
}