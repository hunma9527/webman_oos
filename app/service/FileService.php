    /**
     * 删除文件
     * @param int $fileId 文件ID
     * @return bool 是否成功
     */
    public function delete($fileId)
    {
        $file = File::find($fileId);
        
        if (!$file || $file->status != 1) {
            throw new FileException("文件不存在或已删除");
        }
        
        // 删除关联关系
        FileRelation::where('file_id', $file->id)->delete();
        
        // 删除文件
        return $this->storageService->delete($file);
    }
    
    /**
     * 关联文件到业务
     * @param int $fileId 文件ID
     * @param string $businessType 业务类型
     * @param string $businessId 业务ID
     * @param array $extra 额外信息
     * @return bool 是否成功
     */
    public function associate($fileId, $businessType, $businessId, $extra = [])
    {
        $file = File::find($fileId);
        
        if (!$file || $file->status != 1) {
            throw new FileException("文件不存在或已删除");
        }
        
        // 检查是否已经有关联
        $exists = FileRelation::where('file_id', $fileId)
            ->where('business_type', $businessType)
            ->where('business_id', $businessId)
            ->exists();
            
        if ($exists) {
            return true;
        }
        
        // 创建关联关系
        $relation = new FileRelation();
        $relation->file_id = $fileId;
        $relation->business_type = $businessType;
        $relation->business_id = $businessId;
        $relation->create_time = date('Y-m-d H:i:s');
        $relation->extra = $extra;
        
        return $relation->save();
    }
    
    /**
     * 解除文件与业务的关联
     * @param int $fileId 文件ID
     * @param string $businessType 业务类型
     * @param string $businessId 业务ID
     * @return bool 是否成功
     */
    public function dissociate($fileId, $businessType, $businessId)
    {
        return FileRelation::where('file_id', $fileId)
            ->where('business_type', $businessType)
            ->where('business_id', $businessId)
            ->delete();
    }
    
    /**
     * 获取孤立文件（没有关联关系的文件）
     * @param int $days 超过多少天
     * @param int $limit 限制数量
     * @return array 孤立文件列表
     */
    public function getOrphanFiles($days = 7, $limit = 100)
    {
        // 查找有关联关系的文件ID
        $fileIdsWithRelation = FileRelation::distinct()->pluck('file_id')->toArray();
        
        // 查找没有关联关系且上传时间超过指定天数的文件
        $date = date('Y-m-d H:i:s', time() - $days * 86400);
        
        $query = File::where('status', 1)
            ->where('upload_time', '<', $date);
            
        if (!empty($fileIdsWithRelation)) {
            $query->whereNotIn('id', $fileIdsWithRelation);
        }
        
        $files = $query->limit($limit)->get();
        
        $result = [];
        foreach ($files as $file) {
            $result[] = [
                'id' => $file->id,
                'original_name' => $file->original_name,
                'file_size' => $file->file_size,
                'file_type' => $file->file_type,
                'upload_time' => $file->upload_time,
                'upload_user_id' => $file->upload_user_id,
                'url' => $file->getUrl()
            ];
        }
        
        return $result;
    }
    
    /**
     * 清理孤立文件
     * @param int $days 超过多少天
     * @param int $limit 限制数量
     * @return int 清理数量
     */
    public function cleanOrphanFiles($days = 7, $limit = 100)
    {
        $orphanFiles = $this->getOrphanFiles($days, $limit);
        $count = 0;
        
        foreach ($orphanFiles as $fileInfo) {
            try {
                $file = File::find($fileInfo['id']);
                if ($file && $this->storageService->delete($file)) {
                    $count++;
                }
            } catch (\Exception $e) {
                // 记录错误但继续处理
                error_log("清理孤立文件失败: " . $e->getMessage());
            }
        }
        
        return $count;
    }
}