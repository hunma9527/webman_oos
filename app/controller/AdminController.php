<?php
namespace plugin\oos\app\controller;

use support\Request;
use plugin\oos\app\service\AdminService;
use plugin\oos\app\exception\AdminException;

class AdminController
{
    /**
     * 管理员服务
     * @var AdminService
     */
    protected $adminService;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->adminService = new AdminService();
    }
    
    /**
     * 获取系统统计信息
     * @param Request $request
     * @return \support\Response
     */
    public function stats(Request $request)
    {
        try {
            $stats = $this->adminService->getStats();
            
            // 添加磁盘使用情况
            $stats['disk_usage'] = $this->adminService->getDiskUsage();
            
            return json(['code' => 0, 'msg' => '获取成功', 'data' => $stats]);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '获取统计信息失败: ' . $e->getMessage()]);
        }
    }
    
    /**
     * 获取孤立文件
     * @param Request $request
     * @return \support\Response
     */
    public function orphanFiles(Request $request)
    {
        try {
            $days = (int)$request->get('days', 7);
            $page = (int)$request->get('page', 1);
            $perPage = (int)$request->get('per_page', 20);
            
            $files = $this->adminService->getOrphanFiles($days, $page, $perPage);
            
            return json(['code' => 0, 'msg' => '获取成功', 'data' => $files]);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '获取孤立文件失败: ' . $e->getMessage()]);
        }
    }
    
    /**
     * 清理文件
     * @param Request $request
     * @return \support\Response
     */
    public function cleanFiles(Request $request)
    {
        try {
            $type = $request->post('type');
            
            if ($type === 'orphan') {
                $days = (int)$request->post('days', 7);
                $limit = (int)$request->post('limit', 100);
                
                $result = $this->adminService->cleanOrphanFiles($days, $limit);
                return json(['code' => 0, 'msg' => '清理成功', 'data' => $result]);
            } else if ($type === 'thumbnail') {
                $result = $this->adminService->cleanThumbnailCache();
                return json(['code' => 0, 'msg' => '清理成功', 'data' => $result]);
            } else {
                return json(['code' => 1, 'msg' => '未知的清理类型']);
            }
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '清理文件失败: ' . $e->getMessage()]);
        }
    }
}