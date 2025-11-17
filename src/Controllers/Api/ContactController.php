<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Core\Container;
use App\Helpers\Response as ResponseHelper;
use App\Helpers\Validator;
use App\Helpers\Captcha;
use App\Models\CustomerRequest;
use App\Repositories\CustomerRequestRepository;
use App\Repositories\SiteSettingRepository;

class ContactController
{
    private CustomerRequest $model;
    private CustomerRequestRepository $repository;

    public function __construct()
    {
        $container = Container::getInstance();
        $database = $container->get('database');
        $this->model = new CustomerRequest($database);
        $this->repository = new CustomerRequestRepository($database);
    }

    public function store(Request $request, Response $response, array $params): void
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|min:2|max:100',
            'customer_email' => 'required|email|max:100',
            'customer_phone' => 'max:20',
            'subject' => 'required|min:5|max:200',
            'message' => 'required|min:10|max:2000',
            'captcha_token' => 'required',
        ]);

        if ($validator->fails()) {
            ResponseHelper::validationError($validator->errors());
        }

        $container = Container::getInstance();
        $config = $container->get('config');
        $captchaType = $config->get('security.captcha_type', 'recaptcha');
        $captchaToken = $request->input('captcha_token');

        if (!Captcha::verify($captchaToken, $captchaType)) {
            ResponseHelper::error('CAPTCHA verification failed', null, 422);
        }

        $requestNumber = $this->repository->generateRequestNumber();
        
        $metadata = [];
        if ($request->input('service_id')) {
            $metadata['service_id'] = (int)$request->input('service_id');
        }
        if ($request->input('estimated_budget')) {
            $metadata['estimated_budget'] = $request->input('estimated_budget');
        }

        try {
            $requestId = $this->model->create([
                'request_number' => $requestNumber,
                'customer_name' => $request->input('customer_name'),
                'customer_email' => $request->input('customer_email'),
                'customer_phone' => $request->input('customer_phone'),
                'customer_company' => $request->input('customer_company'),
                'service_id' => $request->input('service_id') ? (int)$request->input('service_id') : null,
                'subject' => $request->input('subject'),
                'message' => $request->input('message'),
                'request_type' => $request->input('request_type', 'general'),
                'priority' => 'normal',
                'status' => 'new',
                'estimated_budget' => $request->input('estimated_budget'),
                'metadata' => !empty($metadata) ? json_encode($metadata) : null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $this->sendNotificationEmail($requestId, $requestNumber);

            $logger = $container->get('logger');
            $logger->info("Contact request created: {$requestNumber}", [
                'request_id' => $requestId,
                'customer_email' => $request->input('customer_email'),
                'subject' => $request->input('subject'),
            ]);

            ResponseHelper::created([
                'request_number' => $requestNumber,
                'request_id' => $requestId,
            ], 'Contact request submitted successfully');

        } catch (\Exception $e) {
            $logger = $container->get('logger');
            $logger->error('Failed to create contact request: ' . $e->getMessage());
            
            ResponseHelper::error('Failed to submit contact request. Please try again.', null, 500);
        }
    }

    private function sendNotificationEmail(int $requestId, string $requestNumber): void
    {
        try {
            $container = Container::getInstance();
            $database = $container->get('database');
            $mailer = $container->get('mailer');
            $config = $container->get('config');

            $contactRequest = $this->model->find($requestId);
            
            if (!$contactRequest) {
                return;
            }

            $settingRepo = new SiteSettingRepository($database);
            $adminEmail = $settingRepo->getValue('admin_email', $config->get('mail.from_address'));
            $siteName = $settingRepo->getValue('site_name', 'Manufacturing Platform');

            $templatePath = __DIR__ . '/../../../templates/email/contact_notification.html';
            $template = file_exists($templatePath) 
                ? file_get_contents($templatePath)
                : $this->getDefaultContactTemplate();

            $body = str_replace('{{SITE_NAME}}', $siteName, $template);
            $body = str_replace('{{REQUEST_NUMBER}}', $requestNumber, $body);
            $body = str_replace('{{CUSTOMER_NAME}}', htmlspecialchars($contactRequest['customer_name']), $body);
            $body = str_replace('{{CUSTOMER_EMAIL}}', htmlspecialchars($contactRequest['customer_email']), $body);
            $body = str_replace('{{CUSTOMER_PHONE}}', htmlspecialchars($contactRequest['customer_phone'] ?? 'N/A'), $body);
            $body = str_replace('{{CUSTOMER_COMPANY}}', htmlspecialchars($contactRequest['customer_company'] ?? 'N/A'), $body);
            $body = str_replace('{{SUBJECT}}', htmlspecialchars($contactRequest['subject']), $body);
            $body = str_replace('{{MESSAGE}}', nl2br(htmlspecialchars($contactRequest['message'])), $body);
            $body = str_replace('{{REQUEST_TYPE}}', htmlspecialchars($contactRequest['request_type']), $body);

            $subject = "New Contact Request - {$requestNumber}";
            
            $mailer->setReplyTo($contactRequest['customer_email'], $contactRequest['customer_name']);
            $mailer->send($adminEmail, $subject, $body);

        } catch (\Exception $e) {
            $logger = $container->get('logger');
            $logger->error('Failed to send contact notification: ' . $e->getMessage());
        }
    }

    private function getDefaultContactTemplate(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #667eea; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        .field { margin: 15px 0; }
        .field strong { display: inline-block; width: 120px; }
        .message-box { background: white; padding: 15px; border-left: 4px solid #667eea; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{SITE_NAME}}</h1>
            <h2>New Contact Request</h2>
        </div>
        <div class="content">
            <h3>Request #{{REQUEST_NUMBER}}</h3>
            
            <div class="field">
                <strong>Name:</strong> {{CUSTOMER_NAME}}
            </div>
            <div class="field">
                <strong>Email:</strong> {{CUSTOMER_EMAIL}}
            </div>
            <div class="field">
                <strong>Phone:</strong> {{CUSTOMER_PHONE}}
            </div>
            <div class="field">
                <strong>Company:</strong> {{CUSTOMER_COMPANY}}
            </div>
            <div class="field">
                <strong>Type:</strong> {{REQUEST_TYPE}}
            </div>
            <div class="field">
                <strong>Subject:</strong> {{SUBJECT}}
            </div>
            
            <h4>Message:</h4>
            <div class="message-box">
                {{MESSAGE}}
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
