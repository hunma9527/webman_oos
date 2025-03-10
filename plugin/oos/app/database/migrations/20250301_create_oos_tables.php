<?php
namespace plugin\oos\app\database\migrations;

use Phinx\Migration\AbstractMigration;

class CreateOosTables extends AbstractMigration
{
    /**
     * 迁移
     */
    public function change()
    {
        // 创建文件表
        $this->table('oos_files', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '文件存储表'
        ])
        ->addColumn('id', 'biginteger', [
            'identity' => true,
            'signed' => false,
            'null' => false,
        ])
        ->addColumn('file_hash', 'char', [
            'limit' => 32,
            'null' => false,
            'comment' => '文件MD5哈希',
        ])
        ->addColumn('original_name', 'string', [
            'limit' => 255,
            'null' => false,
            'comment' => '原始文件名',
        ])
        ->addColumn('storage_name', 'string', [
            'limit' => 255,
            'null' => false,
            'comment' => '存储文件名',
        ])
        ->addColumn('storage_path', 'string', [
            'limit' => 255,
            'null' => false,
            'comment' => '存储路径',
        ])
        ->addColumn('file_ext', 'string', [
            'limit' => 10,
            'null' => false,
            'comment' => '文件扩展名',
        ])
        ->addColumn('file_size', 'biginteger', [
            'signed' => false,
            'null' => false,
            'comment' => '文件大小(字节)',
        ])
        ->addColumn('file_type', 'string', [
            'limit' => 100,
            'null' => false,
            'comment' => '文件MIME类型',
        ])
        ->addColumn('image_width', 'integer', [
            'null' => true,
            'comment' => '图片宽度',
        ])
        ->addColumn('image_height', 'integer', [
            'null' => true,
            'comment' => '图片高度',
        ])
        ->addColumn('upload_time', 'datetime', [
            'null' => false,
            'comment' => '上传时间',
        ])
        ->addColumn('upload_user_id', 'integer', [
            'null' => true,
            'comment' => '上传用户ID',
        ])
        ->addColumn('status', 'boolean', [
            'null' => false,
            'default' => 1,
            'comment' => '状态:1正常,0已删除',
        ])
        ->addColumn('access_count', 'integer', [
            'null' => false,
            'default' => 0,
            'comment' => '访问次数',
        ])
        ->addColumn('last_access_time', 'datetime', [
            'null' => true,
            'comment' => '最后访问时间',
        ])
        ->addColumn('extra', 'json', [
            'null' => true,
            'comment' => '额外信息',
        ])
        ->addIndex(['file_hash'], [
            'name' => 'idx_file_hash',
            'unique' => true,
        ])
        ->addIndex(['upload_time'], [
            'name' => 'idx_upload_time',
        ])
        ->addIndex(['upload_user_id'], [
            'name' => 'idx_upload_user',
        ])
        ->addIndex(['storage_path'], [
            'name' => 'idx_storage_path',
        ])
        ->create();
        
        // 创建文件关联表
        $this->table('oos_file_relations', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'comment' => '文件关联表'
        ])
        ->addColumn('id', 'biginteger', [
            'identity' => true,
            'signed' => false,
            'null' => false,
        ])
        ->addColumn('file_id', 'biginteger', [
            'signed' => false,
            'null' => false,
            'comment' => '文件ID',
        ])
        ->addColumn('business_type', 'string', [
            'limit' => 50,
            'null' => false,
            'comment' => '业务类型',
        ])
        ->addColumn('business_id', 'string', [
            'limit' => 64,
            'null' => false,
            'comment' => '业务ID',
        ])
        ->addColumn('create_time', 'datetime', [
            'null' => false,
            'comment' => '创建时间',
        ])
        ->addColumn('extra', 'json', [
            'null' => true,
            'comment' => '额外信息',
        ])
        ->addIndex(['business_type', 'business_id', 'file_id'], [
            'name' => 'idx_business',
            'unique' => true,
        ])
        ->addIndex(['file_id'], [
            'name' => 'idx_file_id',
        ])
        ->create();
    }
}