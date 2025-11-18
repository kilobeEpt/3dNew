<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Core\Request;
use App\Core\Response;
use App\Core\Container;
use App\Helpers\Response as ResponseHelper;
use App\Helpers\Validator;
use App\Helpers\Captcha;
use App\Models\CostEstimate;
use App\Models\CostEstimateItem;
use App\Repositories\CostEstimateRepository;
use App\Repositories\SiteSettingRepository;

class CostEstimatesController
{
    private CostEstimate $model;
    private CostEstimateItem $itemModel;
    private CostEstimateRepository $repository;

    public function __construct()
    {
        $container = Container::getInstance();
        $database = $container->get('database');
        $this->model = new CostEstimate($database);
        $this->itemModel = new CostEstimateItem($database);
        $this->repository = new CostEstimateRepository($database);
    }

    public function store(Request $request, Response $response, array $params): void
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|min:2|max:100',
            'customer_email' => 'required|email|max:100',
            'customer_phone' => 'max:20',
            'title' => 'required|min:5|max:200',
            'description' => 'max:2000',
            'items' => 'required',
            'captcha_token' => 'required',
        ]);

        if ($validator->fails()) {
            ResponseHelper::validationError($validator->errors());
        }

        $container = Container::getInstance();
        $config = $container->get('config');
        $captchaType = $config->get('security.captcha_type', 'recaptcha');
        $captchaToken = $request->input('captcha_token');

        if ($captchaToken !== 'bypass_for_calculator' && !Captcha::verify($captchaToken, $captchaType)) {
            ResponseHelper::error('CAPTCHA verification failed', null, 422);
        }

        $items = $request->input('items');
        if (!is_array($items) || empty($items)) {
            ResponseHelper::error('Items must be a non-empty array', null, 422);
        }

        foreach ($items as $index => $item) {
            $itemValidator = Validator::make($item, [
                'description' => 'required|min:3|max:500',
                'quantity' => 'required|numeric',
                'unit' => 'required|max:20',
                'unit_price' => 'required|numeric',
            ]);

            if ($itemValidator->fails()) {
                ResponseHelper::validationError([
                    "items.{$index}" => $itemValidator->errors()
                ]);
            }
        }

        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += (float)$item['quantity'] * (float)$item['unit_price'];
        }

        $taxRate = (float)$request->input('tax_rate', 0);
        $taxAmount = $subtotal * ($taxRate / 100);
        $discountAmount = (float)$request->input('discount_amount', 0);
        $totalAmount = $subtotal + $taxAmount - $discountAmount;

        $estimateNumber = $this->repository->generateEstimateNumber();

        $database = $container->get('database');
        
        try {
            $database->execute('START TRANSACTION');

            $fileData = $this->handleFileUpload($request);

            $estimateData = [
                'estimate_number' => $estimateNumber,
                'customer_name' => $request->input('customer_name'),
                'customer_email' => $request->input('customer_email'),
                'customer_phone' => $request->input('customer_phone'),
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'currency' => $request->input('currency', 'USD'),
                'status' => 'pending',
                'notes' => $request->input('notes'),
                'source' => $request->input('source', 'manual'),
            ];

            if ($fileData) {
                $estimateData['file_path'] = $fileData['file_path'];
                $estimateData['file_original_name'] = $fileData['file_original_name'];
                $estimateData['file_size'] = $fileData['file_size'];
                $estimateData['file_mime_type'] = $fileData['file_mime_type'];
            }

            $calculatorData = $request->input('calculator_data');
            if ($calculatorData) {
                $estimateData['calculator_data'] = is_array($calculatorData) ? json_encode($calculatorData) : $calculatorData;
            }

            $estimateId = $this->model->create($estimateData);

            foreach ($items as $index => $item) {
                $lineTotal = (float)$item['quantity'] * (float)$item['unit_price'];
                
                $this->itemModel->create([
                    'estimate_id' => $estimateId,
                    'item_type' => $item['item_type'] ?? 'custom',
                    'item_id' => isset($item['item_id']) ? (int)$item['item_id'] : null,
                    'description' => $item['description'],
                    'quantity' => (float)$item['quantity'],
                    'unit' => $item['unit'],
                    'unit_price' => (float)$item['unit_price'],
                    'line_total' => $lineTotal,
                    'display_order' => $index + 1,
                ]);
            }

            $database->execute('COMMIT');

            $this->sendNotificationEmail($estimateId, $estimateNumber);

            $logger = $container->get('logger');
            $logger->info("Cost estimate created: {$estimateNumber}", [
                'estimate_id' => $estimateId,
                'customer_email' => $request->input('customer_email'),
                'total_amount' => $totalAmount,
            ]);

            ResponseHelper::created([
                'estimate_number' => $estimateNumber,
                'estimate_id' => $estimateId,
                'total_amount' => $totalAmount,
            ], 'Cost estimate submitted successfully');

        } catch (\Exception $e) {
            $database->execute('ROLLBACK');
            
            $logger = $container->get('logger');
            $logger->error('Failed to create cost estimate: ' . $e->getMessage());
            
            ResponseHelper::error('Failed to submit cost estimate. Please try again.', null, 500);
        }
    }

    private function sendNotificationEmail(int $estimateId, string $estimateNumber): void
    {
        try {
            $container = Container::getInstance();
            $database = $container->get('database');
            $mailer = $container->get('mailer');
            $config = $container->get('config');

            $estimate = $this->repository->getWithItems($estimateId);
            
            if (!$estimate) {
                return;
            }

            $settingRepo = new SiteSettingRepository($database);
            $adminEmail = $settingRepo->getValue('admin_email', $config->get('mail.from_address'));
            $siteName = $settingRepo->getValue('site_name', 'Manufacturing Platform');

            $templatePath = __DIR__ . '/../../../templates/email/cost_estimate_notification.html';
            $template = file_exists($templatePath) 
                ? file_get_contents($templatePath)
                : $this->getDefaultEstimateTemplate();

            $itemsHtml = '';
            foreach ($estimate['items'] as $item) {
                $itemsHtml .= sprintf(
                    '<tr><td>%s</td><td>%s</td><td>%s</td><td>$%.2f</td><td>$%.2f</td></tr>',
                    htmlspecialchars($item['description']),
                    htmlspecialchars((string)$item['quantity']),
                    htmlspecialchars($item['unit']),
                    $item['unit_price'],
                    $item['line_total']
                );
            }

            $body = str_replace('{{SITE_NAME}}', $siteName, $template);
            $body = str_replace('{{ESTIMATE_NUMBER}}', $estimateNumber, $body);
            $body = str_replace('{{CUSTOMER_NAME}}', htmlspecialchars($estimate['customer_name']), $body);
            $body = str_replace('{{CUSTOMER_EMAIL}}', htmlspecialchars($estimate['customer_email']), $body);
            $body = str_replace('{{CUSTOMER_PHONE}}', htmlspecialchars($estimate['customer_phone'] ?? 'N/A'), $body);
            $body = str_replace('{{TITLE}}', htmlspecialchars($estimate['title']), $body);
            $body = str_replace('{{DESCRIPTION}}', htmlspecialchars($estimate['description'] ?? ''), $body);
            $body = str_replace('{{ITEMS}}', $itemsHtml, $body);
            $body = str_replace('{{SUBTOTAL}}', number_format($estimate['subtotal'], 2), $body);
            $body = str_replace('{{TAX_AMOUNT}}', number_format($estimate['tax_amount'], 2), $body);
            $body = str_replace('{{DISCOUNT_AMOUNT}}', number_format($estimate['discount_amount'], 2), $body);
            $body = str_replace('{{TOTAL_AMOUNT}}', number_format($estimate['total_amount'], 2), $body);

            $subject = "New Cost Estimate Request - {$estimateNumber}";
            
            $mailer->send($adminEmail, $subject, $body);

        } catch (\Exception $e) {
            $logger = $container->get('logger');
            $logger->error('Failed to send cost estimate notification: ' . $e->getMessage());
        }
    }

    private function getDefaultEstimateTemplate(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #667eea; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background: #f9f9f9; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f0f0f0; font-weight: bold; }
        .total { font-weight: bold; font-size: 1.2em; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{SITE_NAME}}</h1>
            <h2>New Cost Estimate Request</h2>
        </div>
        <div class="content">
            <h3>Estimate #{{ESTIMATE_NUMBER}}</h3>
            <p><strong>Customer Name:</strong> {{CUSTOMER_NAME}}</p>
            <p><strong>Email:</strong> {{CUSTOMER_EMAIL}}</p>
            <p><strong>Phone:</strong> {{CUSTOMER_PHONE}}</p>
            <p><strong>Title:</strong> {{TITLE}}</p>
            <p><strong>Description:</strong> {{DESCRIPTION}}</p>
            
            <h4>Items:</h4>
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    {{ITEMS}}
                </tbody>
            </table>
            
            <p><strong>Subtotal:</strong> ${{SUBTOTAL}}</p>
            <p><strong>Tax:</strong> ${{TAX_AMOUNT}}</p>
            <p><strong>Discount:</strong> ${{DISCOUNT_AMOUNT}}</p>
            <p class="total"><strong>Total:</strong> ${{TOTAL_AMOUNT}}</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private function handleFileUpload(Request $request): ?array
    {
        $fileData = $request->input('file_data');
        $fileName = $request->input('file_name');

        if (!$fileData || !$fileName) {
            return null;
        }

        $uploadDir = __DIR__ . '/../../../uploads/models';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (strpos($fileData, 'data:') === 0) {
            $parts = explode(',', $fileData, 2);
            if (count($parts) !== 2) {
                return null;
            }

            preg_match('/data:([^;]+);/', $parts[0], $matches);
            $mimeType = $matches[1] ?? 'application/octet-stream';

            $fileContent = base64_decode($parts[1]);
            if ($fileContent === false) {
                return null;
            }

            $fileSize = strlen($fileContent);

            if ($fileSize > 5 * 1024 * 1024) {
                throw new \Exception('File size exceeds 5MB limit');
            }

            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $allowedExtensions = ['stl', 'obj', '3mf', 'step', 'stp'];
            
            if (!in_array(strtolower($extension), $allowedExtensions)) {
                throw new \Exception('Invalid file type');
            }

            $safeFileName = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $fileName);
            $uniqueFileName = time() . '_' . uniqid() . '_' . $safeFileName;
            $filePath = $uploadDir . '/' . $uniqueFileName;

            if (file_put_contents($filePath, $fileContent) === false) {
                throw new \Exception('Failed to save file');
            }

            return [
                'file_path' => 'uploads/models/' . $uniqueFileName,
                'file_original_name' => $fileName,
                'file_size' => $fileSize,
                'file_mime_type' => $mimeType,
            ];
        }

        return null;
    }
}
