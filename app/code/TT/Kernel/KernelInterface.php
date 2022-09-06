<?php

namespace TT\Kernel;

use Psr\Container\ContainerInterface;
use Symfony\Contracts\Cache\CacheInterface;

interface KernelInterface
{
    public function init(): void;

    public function terminate(int $status = 0): void;

    public function handleError(int $errno, string $errstr, string $errfile, int $errline): bool;

    public function getContainer(): ContainerInterface;

    public function getGeneratedDir(): string;
}