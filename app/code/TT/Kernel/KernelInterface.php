<?php

namespace TT\Kernel;

use Psr\Container\ContainerInterface;
use Symfony\Contracts\Cache\CacheInterface;

interface KernelInterface
{
    public function getLogDir(): string;


    public function init(): void;

    public function terminate(): void;

    public function handleError(int $errno, string $errstr, string $errfile, int $errline): bool;

    public function getContainer(): ContainerInterface;
}