<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Request;
use App\Core\Response;
use App\Core\Container;
use App\Helpers\Response as ResponseHelper;
use App\Helpers\Validator;
use App\Models\AdminUser;
use App\Models\PasswordResetToken;
use App\Services\JwtService;
use App\Services\AuditLogger;
use App\Services\Mailer;

class AuthController
{
    private Container $container;
    private AdminUser $adminUserModel;
    private JwtService $jwtService;
    private AuditLogger $auditLogger;

    public function __construct()
    {
        $this->container = Container::getInstance();
        $database = $this->container->get('database');
        $this->adminUserModel = new AdminUser($database);
        
        $jwtSecret = $_ENV['JWT_SECRET'] ?? 'your-secret-key-change-in-production';
        $this->jwtService = new JwtService($jwtSecret);
        $this->auditLogger = new AuditLogger($database);
    }

    public function login(Request $request, Response $response, array $params): void
    {
        $data = $request->input();
        
        $rules = [
            'username' => 'required|min:3',
            'password' => 'required|min:6',
        ];

        $errors = Validator::validate($data, $rules);
        if (!empty($errors)) {
            ResponseHelper::validationError($errors);
        }

        $user = $this->adminUserModel->first(['username' => $data['username']]);

        if (!$user || !password_verify($data['password'], $user['password'])) {
            ResponseHelper::error('Invalid credentials', null, 401);
        }

        if ($user['status'] !== 'active') {
            ResponseHelper::error('Account is inactive or suspended', null, 403);
        }

        // Update last login
        $this->adminUserModel->update($user['id'], [
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $request->ip(),
        ]);

        // Log login
        $this->auditLogger->logLogin(
            (int)$user['id'],
            $request->ip(),
            $request->userAgent()
        );

        // Generate tokens
        $accessToken = $this->jwtService->generateAccessToken((int)$user['id'], $user['role']);
        $refreshToken = $this->jwtService->generateRefreshToken((int)$user['id']);

        // Remove password from response
        unset($user['password']);

        ResponseHelper::success([
            'user' => $user,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ], 'Login successful');
    }

    public function logout(Request $request, Response $response, array $params): void
    {
        if (isset($request->adminUser)) {
            $this->auditLogger->logLogout(
                (int)$request->adminUser['id'],
                $request->ip(),
                $request->userAgent()
            );
        }

        ResponseHelper::success(null, 'Logout successful');
    }

    public function refresh(Request $request, Response $response, array $params): void
    {
        $data = $request->input();

        if (!isset($data['refresh_token'])) {
            ResponseHelper::error('Refresh token is required', null, 400);
        }

        $payload = $this->jwtService->verify($data['refresh_token']);

        if (!$payload || $payload['type'] !== 'refresh') {
            ResponseHelper::error('Invalid or expired refresh token', null, 401);
        }

        $user = $this->adminUserModel->find($payload['user_id']);

        if (!$user || $user['status'] !== 'active') {
            ResponseHelper::error('User not found or inactive', null, 401);
        }

        // Generate new access token
        $accessToken = $this->jwtService->generateAccessToken((int)$user['id'], $user['role']);

        ResponseHelper::success([
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ], 'Token refreshed');
    }

    public function requestPasswordReset(Request $request, Response $response, array $params): void
    {
        $data = $request->input();

        $rules = ['email' => 'required|email'];
        $errors = Validator::validate($data, $rules);
        if (!empty($errors)) {
            ResponseHelper::validationError($errors);
        }

        $user = $this->adminUserModel->first(['email' => $data['email']]);

        // Always return success to prevent email enumeration
        if (!$user || $user['status'] !== 'active') {
            ResponseHelper::success(null, 'If the email exists, a password reset link has been sent');
            return;
        }

        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $database = $this->container->get('database');
        $resetTokenModel = new PasswordResetToken($database);
        $resetTokenModel->create([
            'admin_user_id' => $user['id'],
            'token' => hash('sha256', $token),
            'expires_at' => $expiresAt,
        ]);

        // Send email
        try {
            $mailer = $this->container->get('mailer');
            $resetUrl = ($_ENV['APP_URL'] ?? 'http://localhost') . '/admin/reset-password?token=' . $token;
            
            $mailer->send(
                $user['email'],
                'Password Reset Request',
                "Click the following link to reset your password: {$resetUrl}\n\nThis link expires in 1 hour."
            );
        } catch (\Exception $e) {
            // Log error but don't expose it to user
            $logger = $this->container->get('logger');
            $logger->error('Failed to send password reset email: ' . $e->getMessage());
        }

        ResponseHelper::success(null, 'If the email exists, a password reset link has been sent');
    }

    public function resetPassword(Request $request, Response $response, array $params): void
    {
        $data = $request->input();

        $rules = [
            'token' => 'required',
            'password' => 'required|min:8',
            'password_confirmation' => 'required',
        ];

        $errors = Validator::validate($data, $rules);
        if (!empty($errors)) {
            ResponseHelper::validationError($errors);
        }

        if ($data['password'] !== $data['password_confirmation']) {
            ResponseHelper::error('Passwords do not match', null, 400);
        }

        $database = $this->container->get('database');
        $resetTokenModel = new PasswordResetToken($database);

        // Find token
        $hashedToken = hash('sha256', $data['token']);
        $tokenRecord = $resetTokenModel->first(['token' => $hashedToken]);

        if (!$tokenRecord || $tokenRecord['used_at']) {
            ResponseHelper::error('Invalid or expired reset token', null, 400);
        }

        if (strtotime($tokenRecord['expires_at']) < time()) {
            ResponseHelper::error('Reset token has expired', null, 400);
        }

        // Update password
        $hashedPassword = password_hash($data['password'], PASSWORD_ARGON2ID);
        $this->adminUserModel->update($tokenRecord['admin_user_id'], [
            'password' => $hashedPassword,
        ]);

        // Mark token as used
        $resetTokenModel->update($tokenRecord['id'], [
            'used_at' => date('Y-m-d H:i:s'),
        ]);

        // Log password reset
        $this->auditLogger->log(
            (int)$tokenRecord['admin_user_id'],
            'password_reset',
            'admin_user',
            (int)$tokenRecord['admin_user_id'],
            null,
            ['reset_at' => date('Y-m-d H:i:s')],
            $request->ip(),
            $request->userAgent()
        );

        ResponseHelper::success(null, 'Password has been reset successfully');
    }

    public function me(Request $request, Response $response, array $params): void
    {
        if (!isset($request->adminUser)) {
            ResponseHelper::unauthorized();
        }

        $user = $request->adminUser;
        unset($user['password']);

        ResponseHelper::success($user);
    }
}
