<?php

namespace TT\Kernel\Configs\Readers;

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Contracts\Cache\CacheInterface;
use TT\Kernel\Configs\ReaderInterface;
use TT\Kernel\DirectoryReader;

class MainReader implements ReaderInterface
{
    private DirectoryReader $directoryReader;

    private Dotenv $dotenv;

    private CacheInterface $cache;

    public function __construct(
        DirectoryReader $directoryReader,
        Dotenv $dotenv,
        CacheInterface $cache
    ) {
        $this->directoryReader = $directoryReader;
        $this->dotenv = $dotenv;
        $this->cache = $cache;
    }

    public function read(): array
    {
        return $this->cache->get('kernel.app.config', fn() => $this->extractConfig());
    }

    public function extractConfig(): array
    {
        $dotEnv = $this->directoryReader->getFilePath('.env');
        $dotEnvs = [];

        if (file_exists($dotEnv)) {
            $dotEnvs = $this->dotenv->parse(file_get_contents($dotEnv));
        }

        $envConfig = [
            'env' => array_combine(
              array_map('strtolower', array_keys($dotEnvs)),
              array_values($dotEnvs)
            )
        ];

        $fileConfig = (array) @include_once $this->directoryReader->getFilePath('config.php', ['app', 'etc']);

        return array_replace_recursive($fileConfig, $envConfig);
    }
}
