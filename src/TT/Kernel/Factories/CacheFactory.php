<?php

namespace TT\Kernel\Factories;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\NullAdapter;
use TT\Kernel\Config;
use TT\Kernel\DirectoryReader;
use TT\Kernel\FactoryInterface;

class CacheFactory implements FactoryInterface
{
    private Config $config;

    private DirectoryReader $directoryReader;

    public function __construct(
        Config $config,
        DirectoryReader $directoryReader
    ) {
        $this->config = $config;
        $this->directoryReader = $directoryReader;
    }

    public function create(): object
    {
        if ($this->config->get('is_production', false)) {
            return new FilesystemAdapter(
                '',
                0,
                $this->config->get(
                    'cache/file/dir',
                    $this->getDefaultCacheDir()
                )
            );
        }

        return new NullAdapter();
    }

    private function getDefaultCacheDir(): string
    {
        return $this->directoryReader->getRootDir() . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'cache';
    }
}
