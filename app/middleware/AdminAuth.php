<?php
namespace plugin\oos\app\middleware;

use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;
use plugin\oos\app\service\JwtService;
use plugin\oos\app\exception\AuthException;

class AdminAuth implements MiddlewareInterface
{
    /**
     * 处理请求
     * @param Request $request
     * @param callable $handler
     * @return Response
     */
    public function process(Request $request, callable $handler): Response
    {
        // 先检查传统的管理员令牌
        if ($this->checkAdminToken($request)) {
            return $handler($request);
        }
        
        // 再检查JWT令牌
        if ($this->checkJwtToken($request)) {
            return $handler($request);
        }
        
        // 认证失败
        return json([
            'code' => 403,
            'msg' => '无权限访问'
        ]);
    }
    
    /**
     * 检查传统的管理员令牌
     * @param Request $request
     * @return bool
     */
    protected function checkAdminToken(Request $request): bool
    {
        // 获取管理员令牌
        $adminToken = config('plugin.oos.app.admin_token');
        
        // 从请求中获取令牌
        $token = $request->get('admin_token');
        if (empty($token)) {
            $token = $request->header('X-Admin-Token');
        }
        
        return $token === $adminToken;
    }
    
    /**
     * 检查JWT令牌
     * @param Request $request
     * @return bool
     */
    protected function checkJwtToken(Request $request): bool
    {
        // 从请求头获取令牌
        $authHeader = $request->header('Authorization');
        
        if (empty($authHeader)) {
            return false;
        }
        
        // 格式应该是 "Bearer {token}"
        if (strpos($authHeader, 'Bearer ') !== 0) {
            return false;
        }
        
        $token = substr($authHeader, 7);
        
        try {
            $jwtService = new JwtService();
            $payload = $jwtService->decode($token);
            
            // 检查是否是管理员
            if (!isset($payload['is_admin']) || !$payload['is_admin']) {
                return false;
            }
            
            return true;
        } catch (AuthException $e) {
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}