<?php
namespace plugin\oos\app\controller;

use support\Request;
use plugin\oos\app\service\FileService;
use plugin\oos\app\exception\FileException;

class FileController
{
    /**
     * 文件服务
     * @var FileService
     */
    protected $fileService;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->fileService = new FileService();
    }
    
    /**
     * 上传文件
     * @param Request $request
     * @return \support\Response
     */
    public function upload(Request $request)
    {
        try {
            // 获取当前用户ID（根据实际认证系统修改）
            $userId = $this->getCurrentUserId($request);
            
            // 上传文件
            $result = $this->fileService->upload($request, 'file', $userId);
            
            // 关联到业务（如果提供了业务信息）
            $businessType = $request->post('business_type');
            $businessId = $request->post('business_id');
            $extra = $request->post('extra', []);
            
            if ($businessType && $businessId) {
                if (is_string($extra)) {
                    $extra = json_decode($extra, true) ?: [];
                }
                $this->fileService->associate($result['id'], $businessType, $businessId, $extra);
            }
            
            return json(['code' => 0, 'msg' => '上传成功', 'data' => $result]);
        } catch (FileException $e) {
            return json(['code' => 1, 'msg' => $e->getMessage()]);
        } catch (\Exception $e) {
            return json(['code' => 2, 'msg' => '上传失败: ' . $e->getMessage()]);
        }
    }
    
    /**
     * 上传分片
     * @param Request $request
     * @return \support\Response
     */
    public function uploadChunk(Request $request)
    {
        try {
            $uploadId = $request->post('upload_id');
            $chunkIndex = (int)$request->post('chunk_index');
            
            // 如果没有uploadId，则初始化分片上传
            if (empty($uploadId)) {
                $filename = $request->post('filename');
                $totalChunks = (int)$request->post('total_chunks', 1);
                $userId = $this->getCurrentUserId($request);
                
                $result = $this->fileService->initChunkUpload($filename, $totalChunks, $userId);
                return json(['code' => 0, 'msg' => '初始化成功', 'data' => $result]);
            }
            
            // 上传分片
            $result = $this->fileService->uploadChunk($request, $uploadId, $chunkIndex);
            return json(['code' => 0, 'msg' => '分片上传成功', 'data' => $result]);
        } catch (FileException $e) {
            return json(['code' => 1, 'msg' => $e->getMessage()]);
        } catch (\Exception $e) {
            return json(['code' => 2, 'msg' => '分片上传失败: ' . $e->getMessage()]);
        }
    }
    
    /**
     * 合并分片
     * @param Request $request
     * @return \support\Response
     */
    public function mergeChunks(Request $request)
    {
        try {
            $uploadId = $request->post('upload_id');
            
            if (empty($uploadId)) {
                return json(['code' => 1, 'msg' => '缺少上传ID']);
            }
            
            // 合并分片
            $result = $this->fileService->mergeChunks($uploadId);
            
            // 关联到业务（如果提供了业务信息）
            $businessType = $request->post('business_type');
            $businessId = $request->post('business_id');
            $extra = $request->post('extra', []);
            
            if ($businessType && $businessId) {
                if (is_string($extra)) {
                    $extra = json_decode($extra, true) ?: [];
                }
                $this->fileService->associate($result['id'], $businessType, $businessId, $extra);
            }
            
            return json(['code' => 0, 'msg' => '合并成功', 'data' => $result]);
        } catch (FileException $e) {
            return json(['code' => 1, 'msg' => $e->getMessage()]);
        } catch (\Exception $e) {
            return json(['code' => 2, 'msg' => '合并失败: ' . $e->getMessage()]);
        }
    }
    
    /**
     * 下载文件
     * @param Request $request
     * @param int $id
     * @return \support\Response
     */
    public function download(Request $request, $id)
    {
        try {
            $fileInfo = $this->fileService->download($id);
            
            // 检查文件是否存在
            if (!file_exists($fileInfo['path'])) {
                return json(['code' => 1, 'msg' => '文件不存在或已被删除']);
            }
            
            // 设置下载头
            $response = response()->file($fileInfo['path']);
            $response->withHeaders([
                'Content-Type' => $fileInfo['file_type'],
                'Content-Disposition' => 'attachment; filename="' . urlencode($fileInfo['original_name']) . '"',
                'Content-Length' => $fileInfo['file_size']
            ]);
            
            return $response;
        } catch (FileException $e) {
            return json(['code' => 1, 'msg' => $e->getMessage()]);
        } catch (\Exception $e) {
            return json(['code' => 2, 'msg' => '下载失败: ' . $e->getMessage()]);
        }
    }
    
    /**
     * 预览文件
     * @param Request $request
     * @param int $id
     * @return \support\Response
     */
    public function preview(Request $request, $id)
    {
        try {
            $options = [
                'thumbnail' => $request->get('thumbnail'),
                'width' => (int)$request->get('width'),
                'height' => (int)$request->get('height'),
                'keep_ratio' => $request->get('keep_ratio', true),
                'watermark' => $request->get('watermark')
            ];
            
            $fileInfo = $this->fileService->preview($id, $options);
            
            // 如果有特定处理后的URL，直接重定向
            if (isset($fileInfo['url'])) {
                return redirect($fileInfo['url']);
            }
            
            // 检查文件是否存在
            $filePath = isset($fileInfo['path']) ? $fileInfo['path'] : null;
            if (!$filePath || !file_exists($filePath)) {
                return json(['code' => 1, 'msg' => '文件不存在或已被删除']);
            }
            
            // 设置响应头
            $response = response()->file($filePath);
            $response->withHeaders([
                'Content-Type' => $fileInfo['file_type'],
                'Content-Length' => $fileInfo['file_size'] ?? filesize($filePath),
                'Cache-Control' => 'max-age=86400'
            ]);
            
            return $response;
        } catch (FileException $e) {
            return json(['code' => 1, 'msg' => $e->getMessage()]);
        } catch (\Exception $e) {
            return json(['code' => 2, 'msg' => '预览失败: ' . $e->getMessage()]);
        }
    }
    
    /**
     * 文件列表
     * @param Request $request
     * @return \support\Response
     */
    public function list(Request $request)
    {
        try {
            $params = [
                'upload_user_id' => $request->get('upload_user_id'),
                'file_type' => $request->get('file_type'),
                'file_ext' => $request->get('file_ext'),
                'start_time' => $request->get('start_time'),
                'end_time' => $request->get('end_time'),
                'keyword' => $request->get('keyword'),
                'business_type' => $request->get('business_type'),
                'business_id' => $request->get('business_id'),
                'order_by' => $request->get('order_by', 'upload_time'),
                'order_direction' => $request->get('order_direction', 'desc')
            ];
            
            $page = (int)$request->get('page', 1);
            $perPage = (int)$request->get('per_page', 20);
            
            $result = $this->fileService->list($params, $page, $perPage);
            
            return json(['code' => 0, 'msg' => '获取成功', 'data' => $result]);
        } catch (\Exception $e) {
            return json(['code' => 1, 'msg' => '获取文件列表失败: ' . $e->getMessage()]);
        }
    }
    
    /**
     * 文件信息
     * @param Request $request
     * @param int $id
     * @return \support\Response
     */
    public function info(Request $request, $id)
    {
        try {
            $fileInfo = $this->fileService->info($id);
            
            return json(['code' => 0, 'msg' => '获取成功', 'data' => $fileInfo]);
        } catch (FileException $e) {
            return json(['code' => 1, 'msg' => $e->getMessage()]);
        } catch (\Exception $e) {
            return json(['code' => 2, 'msg' => '获取文件信息失败: ' . $e->getMessage()]);
        }
    }
    
    /**
     * 删除文件
     * @param Request $request
     * @return \support\Response
     */
    public function delete(Request $request)
    {
        try {
            $id = $request->post('id');
            
            if (empty($id)) {
                return json(['code' => 1, 'msg' => '缺少文件ID']);
            }
            
            $result = $this->fileService->delete($id);
            
            return json([
                'code' => 0, 
                'msg' => $result ? '删除成功' : '删除失败',
                'data' => ['success' => $result]
            ]);
        } catch (FileException $e) {
            return json(['code' => 1, 'msg' => $e->getMessage()]);
        } catch (\Exception $e) {
            return json(['code' => 2, 'msg' => '删除文件失败: ' . $e->getMessage()]);
        }
    }
    
    /**
     * 获取当前用户ID
     * @param Request $request
     * @return int|null
     */
    protected function getCurrentUserId(Request $request)
    {
        // 根据实际认证系统修改
        // 示例：从会话中获取
        // return session('user_id');
        
        // 示例：从请求头获取
        // return $request->header('X-User-Id');
        
        // 示例：从请求参数获取
        return $request->post('user_id') ?: $request->get('user_id');
    }
}