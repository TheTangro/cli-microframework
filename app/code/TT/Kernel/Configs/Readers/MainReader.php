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

        if (file_exists($dotEnv)) {
            $this->dotenv->load($dotEnv);
        }

        $config = [
            'db_connection' => [
                'user' => getenv('DB_USER'),
                'password' => getenv('DB_PASS'),
                'host' => getenv('DB_HOST'),
            ]
        ];

        $fileConfig = (array) @include_once $this->directoryReader->getFilePath('config.php', ['app', 'etc']);

        return array_merge($fileConfig, $config);
    }
}
