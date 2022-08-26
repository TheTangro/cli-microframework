<?php

use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Console\Application;
use TT\Kernel\Config;
use TT\Kernel\Configs\Reader;
use TT\Kernel\Exceptions\AppException;
use TT\Kernel\KernelInterface;

class Kernel implements KernelInterface
{
    private ?ContainerInterface $container = null;

    public function getLogDir(): string
    {
        return BP . '/var/log/';
    }

    public function init(): void
    {
        set_error_handler([$this, 'handleError']);
        register_shutdown_function([$this, 'terminate']);
        $this->container = $this->configureDiContainer();
    }

    public function run(): void
    {
        $application = $this->container->get(Application::class);
        $application->run();
    }

    public function terminate(): void
    {
    }

    public function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        throw new \Exception("Error: {$errstr}. File: {$errfile}:{$errline}");
    }

    public function getContainer(): ContainerInterface
    {
        if ($this->container === null) {
            throw new AppException('App is not initialized');
        }

        return $this->container;
    }

    /**
     * @return \DI\ContainerBuilder
     */
    public function getContainerBuilder(): \DI\ContainerBuilder
    {
        $definitions = [
            \Symfony\Contracts\Cache\CacheInterface::class => \Di\factory(
                'TT\Kernel\Factories\CacheFactory::create'
            )
        ];
        $containerBuilder = new \DI\ContainerBuilder();
        $containerBuilder->addDefinitions($definitions);

        return $containerBuilder;
    }

    /**
     * @return \Di\Container
     * @throws AppException
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function configureDiContainer(): \Di\Container
    {
        $containerBuilder = $this->getContainerBuilder();
        $temporaryContainer = $containerBuilder->build();
        $reader = $temporaryContainer->make(Reader::class, ['container' => $temporaryContainer]);
        $config = $reader->read('di');
        $appConfig = new Config($reader->read('main'));
        $containerBuilder = $this->getContainerBuilder();
        $containerBuilder->addDefinitions(array_merge($config, [
            Config::class => $appConfig
        ]));

        if ($appConfig->get('is_production')) {
            $containerBuilder->enableCompilation(BP . '/generated/');

        }

        return $containerBuilder->build();
    }
}
