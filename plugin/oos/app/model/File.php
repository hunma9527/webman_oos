<?php
namespace plugin\oos\app\model;

use support\Model;

class File extends Model
{
    /**
     * 与模型关联的表名
     * @var string
     */
    protected $table = 'oos_files';
    
    /**
     * 指示模型是否主动维护时间戳
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * 可以被批量赋值的属性
     * @var array
     */
    protected $fillable = [
        'file_hash', 'original_name', 'storage_name', 'storage_path',
        'file_ext', 'file_size', 'file_type', 'image_width', 'image_height',
        'upload_time', 'upload_user_id', 'status', 'access_count', 'last_access_time', 'extra'
    ];
    
    /**
     * 应该转换为特定类型的属性
     * @var array
     */
    protected $casts = [
        'file_size' => 'integer',
        'image_width' => 'integer',
        'image_height' => 'integer',
        'upload_time' => 'datetime',
        'last_access_time' => 'datetime',
        'status' => 'integer',
        'access_count' => 'integer',
        'extra' => 'json',
    ];
    
    /**
     * 获取文件的完整URL
     * @return string
     */
    public function getUrl()
    {
        $config = config('plugin.oos.app.storage');
        $disk = $config['disks'][$config['default']];
        return $disk['url'] . '/' . $this->storage_path . '/' . $this->storage_name;
    }
    
    /**
     * 获取文件的完整本地路径
     * @return string
     */
    public function getPath()
    {
        $config = config('plugin.oos.app.storage');
        $disk = $config['disks'][$config['default']];
        return $disk['root'] . '/' . $this->storage_path . '/' . $this->storage_name;
    }
    
    /**
     * 记录文件被访问
     * @return bool
     */
    public function recordAccess()
    {
        $this->access_count++;
        $this->last_access_time = date('Y-m-d H:i:s');
        return $this->save();
    }
    
    /**
     * 获取文件的关联关系
     */
    public function relations()
    {
        return $this->hasMany(FileRelation::class, 'file_id', 'id');
    }
}