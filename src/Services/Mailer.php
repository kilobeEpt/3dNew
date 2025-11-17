<?php

declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer
{
    private array $config;
    private PHPMailer $mailer;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->mailer = new PHPMailer(true);
        $this->configure();
    }

    private function configure(): void
    {
        $this->mailer->isSMTP();
        $this->mailer->Host = $this->config['host'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $this->config['username'];
        $this->mailer->Password = $this->config['password'];
        $this->mailer->SMTPSecure = $this->config['encryption'] === 'ssl' 
            ? PHPMailer::ENCRYPTION_SMTPS 
            : PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = $this->config['port'];
        $this->mailer->setFrom($this->config['from_address'], $this->config['from_name']);
        $this->mailer->isHTML(true);
    }

    public function send(string $to, string $subject, string $body, string $altBody = ''): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->AltBody = $altBody ?: strip_tags($body);

            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Mailer Error: {$this->mailer->ErrorInfo}");
            return false;
        }
    }

    public function sendMultiple(array $recipients, string $subject, string $body, string $altBody = ''): bool
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();
            
            foreach ($recipients as $recipient) {
                $this->mailer->addAddress($recipient);
            }
            
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->AltBody = $altBody ?: strip_tags($body);

            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Mailer Error: {$this->mailer->ErrorInfo}");
            return false;
        }
    }

    public function addAttachment(string $path, string $name = ''): self
    {
        try {
            $this->mailer->addAttachment($path, $name);
        } catch (Exception $e) {
            error_log("Attachment Error: {$e->getMessage()}");
        }
        return $this;
    }

    public function setReplyTo(string $email, string $name = ''): self
    {
        try {
            $this->mailer->addReplyTo($email, $name);
        } catch (Exception $e) {
            error_log("Reply-To Error: {$e->getMessage()}");
        }
        return $this;
    }
}
