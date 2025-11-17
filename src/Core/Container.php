<?php

declare(strict_types=1);

namespace App\Core;

class Container
{
    private static ?Container $instance = null;
    private array $services = [];

    private function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function set(string $name, $service): void
    {
        $this->services[$name] = $service;
    }

    public function get(string $name)
    {
        if (!isset($this->services[$name])) {
            throw new \Exception("Service '{$name}' not found in container");
        }
        return $this->services[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->services[$name]);
    }
}
