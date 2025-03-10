<?php
namespace plugin\oos\app\model;

use support\Model;

class FileRelation extends Model
{
    /**
     * 与模型关联的表名
     * @var string
     */
    protected $table = 'oos_file_relations';
    
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
        'file_id', 'business_type', 'business_id', 'create_time', 'extra'
    ];
    
    /**
     * 应该转换为特定类型的属性
     * @var array
     */
    protected $casts = [
        'create_time' => 'datetime',
        'extra' => 'json',
    ];
    
    /**
     * 获取关联的文件
     */
    public function file()
    {
        return $this->belongsTo(File::class, 'file_id', 'id');
    }
}