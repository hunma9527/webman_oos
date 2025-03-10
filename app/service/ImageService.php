            case 'image/gif':
                imagegif($source, $targetPath);
                break;
            case 'image/webp':
                imagewebp($source, $targetPath, $quality);
                break;
        }
        
        // 释放资源
        imagedestroy($source);
        imagedestroy($watermark);
    }
    
    /**
     * 使用Imagick添加水印
     * @param string $sourcePath 源文件路径
     * @param string $targetPath 目标文件路径
     */
    protected function applyImagickWatermark($sourcePath, $targetPath)
    {
        // 加载目标图片
        $image = new \Imagick($sourcePath);
        
        // 加载水印图片
        $watermarkImage = $this->config['image']['watermark']['image'];
        if (!file_exists($watermarkImage)) {
            throw new ImageProcessException("水印图片不存在: {$watermarkImage}");
        }
        
        $watermark = new \Imagick($watermarkImage);
        
        // 设置水印透明度
        $opacity = $this->config['image']['watermark']['opacity'] / 100;
        $watermark->evaluateImage(\Imagick::EVALUATE_MULTIPLY, $opacity, \Imagick::CHANNEL_ALPHA);
        
        // 获取图片尺寸
        $imageWidth = $image->getImageWidth();
        $imageHeight = $image->getImageHeight();
        $watermarkWidth = $watermark->getImageWidth();
        $watermarkHeight = $watermark->getImageHeight();
        
        // 计算位置
        $position = $this->config['image']['watermark']['position'];
        $x = 0;
        $y = 0;
        
        switch ($position) {
            case 'top-left':
                $x = 10;
                $y = 10;
                break;
            case 'top-right':
                $x = $imageWidth - $watermarkWidth - 10;
                $y = 10;
                break;
            case 'bottom-left':
                $x = 10;
                $y = $imageHeight - $watermarkHeight - 10;
                break;
            case 'bottom-right':
                $x = $imageWidth - $watermarkWidth - 10;
                $y = $imageHeight - $watermarkHeight - 10;
                break;
            case 'center':
                $x = ($imageWidth - $watermarkWidth) / 2;
                $y = ($imageHeight - $watermarkHeight) / 2;
                break;
        }
        
        // 合成图片
        $image->compositeImage($watermark, \Imagick::COMPOSITE_OVER, $x, $y);
        
        // 保存图片
        $image->setImageCompressionQuality($this->config['image']['quality']);
        $image->writeImage($targetPath);
        
        // 释放资源
        $image->clear();
        $watermark->clear();
    }
    
    /**
     * 改变图片尺寸
     * @param File $file 文件对象
     * @param int $width 宽度
     * @param int $height 高度
     * @param bool $keepRatio 是否保持宽高比
     * @return string 调整后的图片URL
     */
    public function resize(File $file, $width, $height, $keepRatio = true)
    {
        // 检查是否为图片
        if (strpos($file->file_type, 'image/') !== 0) {
            throw new ImageProcessException("非图片文件不能调整尺寸");
        }
        
        // 确定调整后图片路径
        $diskConfig = $this->config['disks'][$this->config['default']];
        $sourcePath = $diskConfig['root'] . '/' . $file->storage_path . '/' . $file->storage_name;
        $resizeDir = $diskConfig['root'] . '/' . $file->storage_path . '/resized';
        $baseFilename = pathinfo($file->storage_name, PATHINFO_FILENAME);
        $extension = pathinfo($file->storage_name, PATHINFO_EXTENSION);
        $resizeName = $baseFilename . '_' . $width . 'x' . $height . ($keepRatio ? '_ratio' : '') . '.' . $extension;
        $resizePath = $resizeDir . '/' . $resizeName;
        
        // 检查调整大小的图片是否已存在
        if (file_exists($resizePath)) {
            return $diskConfig['url'] . '/' . $file->storage_path . '/resized/' . $resizeName;
        }
        
        // 确保调整大小的目录存在
        if (!is_dir($resizeDir)) {
            mkdir($resizeDir, 0755, true);
        }
        
        // 调整图片大小
        $driver = $this->config['image']['driver'];
        if ($driver === 'gd') {
            $this->resizeGdImage($sourcePath, $resizePath, $width, $height, $keepRatio);
        } else if ($driver === 'imagick') {
            $this->resizeImagickImage($sourcePath, $resizePath, $width, $height, $keepRatio);
        } else {
            throw new ImageProcessException("不支持的图片处理驱动: {$driver}");
        }
        
        return $diskConfig['url'] . '/' . $file->storage_path . '/resized/' . $resizeName;
    }
    
    /**
     * 使用GD调整图片尺寸
     * @param string $sourcePath 源文件路径
     * @param string $targetPath 目标文件路径
     * @param int $width 宽度
     * @param int $height 高度
     * @param bool $keepRatio 是否保持宽高比
     */
    protected function resizeGdImage($sourcePath, $targetPath, $width, $height, $keepRatio)
    {
        // 获取图片信息
        $info = getimagesize($sourcePath);
        $mime = $info['mime'];
        
        // 创建图片实例
        switch ($mime) {
            case 'image/jpeg':
                $source = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $source = imagecreatefrompng($sourcePath);
                break;
            case 'image/gif':
                $source = imagecreatefromgif($sourcePath);
                break;
            case 'image/webp':
                $source = imagecreatefromwebp($sourcePath);
                break;
            default:
                throw new ImageProcessException("不支持的图片类型: {$mime}");
        }
        
        // 获取原始尺寸
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        
        // 计算新尺寸
        $newWidth = $width;
        $newHeight = $height;
        
        if ($keepRatio) {
            $ratio = min($width / $sourceWidth, $height / $sourceHeight);
            $newWidth = round($sourceWidth * $ratio);
            $newHeight = round($sourceHeight * $ratio);
        }
        
        // 创建新图片
        $target = imagecreatetruecolor($newWidth, $newHeight);
        
        // 处理透明度（PNG和GIF）
        if ($mime == 'image/png' || $mime == 'image/gif') {
            imagealphablending($target, false);
            imagesavealpha($target, true);
            $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
            imagefilledrectangle($target, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        // 调整图片
        imagecopyresampled($target, $source, 0, 0, 0, 0, $newWidth, $newHeight, $sourceWidth, $sourceHeight);
        
        // 输出图片
        $quality = $this->config['image']['quality'];
        switch ($mime) {
            case 'image/jpeg':
                imagejpeg($target, $targetPath, $quality);
                break;
            case 'image/png':
                $pngQuality = ($quality - 100) / 11.111111;
                $pngQuality = round(abs($pngQuality));
                imagepng($target, $targetPath, $pngQuality);
                break;
            case 'image/gif':
                imagegif($target, $targetPath);
                break;
            case 'image/webp':
                imagewebp($target, $targetPath, $quality);
                break;
        }
        
        // 释放资源
        imagedestroy($source);
        imagedestroy($target);
    }
    
    /**
     * 使用Imagick调整图片尺寸
     * @param string $sourcePath 源文件路径
     * @param string $targetPath 目标文件路径
     * @param int $width 宽度
     * @param int $height 高度
     * @param bool $keepRatio 是否保持宽高比
     */
    protected function resizeImagickImage($sourcePath, $targetPath, $width, $height, $keepRatio)
    {
        $imagick = new \Imagick($sourcePath);
        
        if ($keepRatio) {
            $imagick->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1, true);
        } else {
            $imagick->resizeImage($width, $height, \Imagick::FILTER_LANCZOS, 1, false);
        }
        
        $imagick->setImageCompressionQuality($this->config['image']['quality']);
        $imagick->writeImage($targetPath);
        $imagick->clear();
    }
}