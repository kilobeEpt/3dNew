<?php

declare(strict_types=1);

namespace App\Services;

use Exception;

class JwtService
{
    private string $secret;
    private string $algorithm = 'HS256';
    private int $expirationTime = 3600; // 1 hour
    private int $refreshExpirationTime = 604800; // 7 days

    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    public function generate(array $payload, bool $isRefreshToken = false): string
    {
        $header = [
            'typ' => 'JWT',
            'alg' => $this->algorithm,
        ];

        $expiration = $isRefreshToken ? $this->refreshExpirationTime : $this->expirationTime;
        
        $payload['iat'] = time();
        $payload['exp'] = time() + $expiration;

        $base64Header = $this->base64UrlEncode(json_encode($header));
        $base64Payload = $this->base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, $this->secret, true);
        $base64Signature = $this->base64UrlEncode($signature);

        return $base64Header . '.' . $base64Payload . '.' . $base64Signature;
    }

    public function verify(string $token): ?array
    {
        try {
            $parts = explode('.', $token);
            
            if (count($parts) !== 3) {
                return null;
            }

            [$base64Header, $base64Payload, $base64Signature] = $parts;

            $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, $this->secret, true);
            $expectedSignature = $this->base64UrlEncode($signature);

            if (!hash_equals($expectedSignature, $base64Signature)) {
                return null;
            }

            $payload = json_decode($this->base64UrlDecode($base64Payload), true);

            if (!isset($payload['exp']) || $payload['exp'] < time()) {
                return null;
            }

            return $payload;
        } catch (Exception $e) {
            return null;
        }
    }

    public function decode(string $token): ?array
    {
        try {
            $parts = explode('.', $token);
            
            if (count($parts) !== 3) {
                return null;
            }

            $payload = json_decode($this->base64UrlDecode($parts[1]), true);
            return $payload;
        } catch (Exception $e) {
            return null;
        }
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    public function generateAccessToken(int $userId, string $role): string
    {
        return $this->generate([
            'user_id' => $userId,
            'role' => $role,
            'type' => 'access',
        ]);
    }

    public function generateRefreshToken(int $userId): string
    {
        return $this->generate([
            'user_id' => $userId,
            'type' => 'refresh',
        ], true);
    }
}
