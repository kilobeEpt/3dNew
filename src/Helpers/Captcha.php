<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Core\Container;

class Captcha
{
    public static function verify(string $token, string $type = 'recaptcha'): bool
    {
        $container = Container::getInstance();
        $config = $container->get('config');

        if ($type === 'recaptcha') {
            return self::verifyRecaptcha($token, $config);
        } elseif ($type === 'hcaptcha') {
            return self::verifyHcaptcha($token, $config);
        }

        return false;
    }

    private static function verifyRecaptcha(string $token, $config): bool
    {
        $secret = $config->get('security.recaptcha_secret');
        
        if (!$secret) {
            return true;
        }

        $response = file_get_contents(
            'https://www.google.com/recaptcha/api/siteverify?' . http_build_query([
                'secret' => $secret,
                'response' => $token,
                'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
            ])
        );

        if (!$response) {
            return false;
        }

        $result = json_decode($response, true);
        return isset($result['success']) && $result['success'] === true;
    }

    private static function verifyHcaptcha(string $token, $config): bool
    {
        $secret = $config->get('security.hcaptcha_secret');
        
        if (!$secret) {
            return true;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://hcaptcha.com/siteverify');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'secret' => $secret,
            'response' => $token,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) {
            return false;
        }

        $result = json_decode($response, true);
        return isset($result['success']) && $result['success'] === true;
    }
}
