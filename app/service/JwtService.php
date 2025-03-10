<?php
namespace plugin\oos\app\service;

use plugin\oos\app\exception\AuthException;

class JwtService
{
    /**
     * 密钥
     * @var string
     */
    protected $secret;
    
    /**
     * 构造函数
     */
    public function __construct()
    {
        // 从配置中获取密钥，如果没有则使用默认值
        $this->secret = config('plugin.oos.app.jwt_secret', 'oos-secret-key');
    }
    
    /**
     * 生成 JWT 令牌
     * @param array $payload 负载数据
     * @param int $expiry 过期时间（秒）
     * @return string 令牌
     */
    public function encode(array $payload, $expiry = 3600)
    {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];
        
        $payload['iat'] = time();
        $payload['exp'] = time() + $expiry;
        
        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));
        
        $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $this->secret, true);
        $signatureEncoded = $this->base64UrlEncode($signature);
        
        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }
    
    /**
     * 验证并解析 JWT 令牌
     * @param string $token JWT 令牌
     * @return array 负载数据
     * @throws AuthException 如果令牌无效
     */
    public function decode($token)
    {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            throw new AuthException('无效的令牌格式');
        }
        
        list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;
        
        // 验证签名
        $signature = $this->base64UrlDecode($signatureEncoded);
        $expectedSignature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $this->secret, true);
        
        if (!hash_equals($signature, $expectedSignature)) {
            throw new AuthException('令牌签名无效');
        }
        
        // 解析负载
        $payload = json_decode($this->base64UrlDecode($payloadEncoded), true);
        
        // 验证过期时间
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new AuthException('令牌已过期');
        }
        
        return $payload;
    }
    
    /**
     * Base64 URL 编码
     * @param string $data 数据
     * @return string 编码后的字符串
     */
    protected function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    /**
     * Base64 URL 解码
     * @param string $data 编码数据
     * @return string 解码后的数据
     */
    protected function base64UrlDecode($data)
    {
        return base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
    }
}