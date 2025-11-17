<?php

declare(strict_types=1);

namespace App\Core;

class Response
{
    private int $statusCode = 200;
    private array $headers = [];
    private $content = '';

    public function status(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function json($data, int $statusCode = 200): void
    {
        $this->statusCode = $statusCode;
        $this->header('Content-Type', 'application/json');
        $this->content = json_encode($data);
        $this->send();
    }

    public function html(string $content, int $statusCode = 200): void
    {
        $this->statusCode = $statusCode;
        $this->header('Content-Type', 'text/html');
        $this->content = $content;
        $this->send();
    }

    public function text(string $content, int $statusCode = 200): void
    {
        $this->statusCode = $statusCode;
        $this->header('Content-Type', 'text/plain');
        $this->content = $content;
        $this->send();
    }

    public function redirect(string $url, int $statusCode = 302): void
    {
        $this->statusCode = $statusCode;
        $this->header('Location', $url);
        $this->send();
    }

    public function send(): void
    {
        http_response_code($this->statusCode);
        
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }
        
        echo $this->content;
        exit;
    }

    public function download(string $filePath, string $filename = null): void
    {
        if (!file_exists($filePath)) {
            $this->status(404)->text('File not found');
            return;
        }

        $filename = $filename ?? basename($filePath);
        $this->header('Content-Type', 'application/octet-stream');
        $this->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $this->header('Content-Length', (string)filesize($filePath));
        
        http_response_code($this->statusCode);
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }
        
        readfile($filePath);
        exit;
    }
}
