<?php

namespace TT\Kernel;

class Config
{
    private array $config;

    public function __construct(
       array $config = []
    ) {
        $this->config = $config;
    }

    public function get(string $path, $defaultValue = null)
    {
        $pathParts = explode('/', $path);
        $config = $this->config;

        while (!empty($pathParts)) {
            $key = array_shift($pathParts);
            $config = $config[$key] ?? null;
        }

        return $config ?? $defaultValue;
    }
}
