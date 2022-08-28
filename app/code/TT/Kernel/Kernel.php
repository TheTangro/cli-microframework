<?php

namespace TT\Kernel;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use TT\Kernel\Configs\Reader;
use TT\Kernel\Exceptions\AppException;

class Kernel implements KernelInterface
{
    private ?ContainerInterface $container = null;

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

    private function findAllModules(\Di\Container $container): void
    {
        $cache = $container->get(\Symfony\Contracts\Cache\CacheInterface::class);
        $registrationPHPs = $cache->get('kernel.registrations.files', static function() {
            $possiblePaths = [
                'app/code' => '*' . DIRECTORY_SEPARATOR . '*',
                'vendor' => '*',
                'vendor/' => 'thetangro/yaff/app/code/TT/*'
            ];
            $files = [];

            foreach ($possiblePaths as $possiblePath => $subPartRegex) {
                $startPath = BP . DIRECTORY_SEPARATOR . $possiblePath
                    . DIRECTORY_SEPARATOR . $subPartRegex . DIRECTORY_SEPARATOR . 'registration.php';
                $files = array_merge($files, glob($startPath));
            }

            return $files;
        });

        foreach ($registrationPHPs as $registrationPHP) {
            include_once $registrationPHP;
        }
    }

    /**
     * @return \Di\Container
     * @throws AppException
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    private function configureDiContainer(): \Di\Container
    {
        $containerBuilder = $this->getContainerBuilder();
        $temporaryContainer = $containerBuilder->build();
        $reader = $temporaryContainer->make(Reader::class, ['container' => $temporaryContainer]);
        $this->findAllModules($temporaryContainer);
        $appConfig = new Config($reader->read('main'));
        $containerBuilder = $this->getContainerBuilder();
        $containerBuilder->addDefinitions([Config::class => $appConfig]);
        $temporaryContainer = $containerBuilder->build();
        $reader = $temporaryContainer->make(Reader::class, ['container' => $temporaryContainer]);

        $config = $reader->read('di');
        $containerBuilder = $this->getContainerBuilder();
        $containerBuilder->addDefinitions($config);
        $containerBuilder->addDefinitions([\TT\Kernel\KernelInterface::class => $this]);
        $containerBuilder->addDefinitions([Config::class => $appConfig]);
        $temporaryContainer = $containerBuilder->build();
        /** @var \TT\Kernel\Events\EventManagerInterface $eventManager **/
        $eventManager = $temporaryContainer->get(\TT\Kernel\Events\EventManagerInterface::class);
        $additionalConfigs = [];
        $eventManager->dispatchEvent(
            'kernel.container.configure',
            ['additional_definitions' => &$additionalConfigs]
        );
        $containerBuilder = $this->getContainerBuilder();
        $containerBuilder->addDefinitions($config);
        $containerBuilder->addDefinitions([Config::class => $appConfig]);
        $containerBuilder->addDefinitions($additionalConfigs);
        $containerBuilder->addDefinitions([\TT\Kernel\KernelInterface::class => $this]);

        if ($appConfig->get('is_production')) {
            $containerBuilder->enableCompilation($this->getGeneratedDir());
        }

        return $containerBuilder->build();
    }

    public function getGeneratedDir(): string
    {
        return BP . '/generated/';
    }
}
