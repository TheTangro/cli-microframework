<?php

namespace TT\ORM\Model\Connection;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\ORMSetup;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Contracts\Cache\CacheInterface;
use TT\Kernel\ComponentRegistrar;
use TT\Kernel\Config;
use TT\Kernel\DirectoryReader;
use TT\Kernel\KernelInterface;

class ConfigLoader
{
    private ComponentRegistrar $componentRegistrar;

    private CacheInterface $cache;

    private DirectoryReader $directoryReader;

    private KernelInterface $kernel;

    private Config $config;

    public function __construct(
        ComponentRegistrar $componentRegistrar,
        CacheInterface $cache,
        DirectoryReader $directoryReader,
        KernelInterface $kernel,
        Config $config
    ) {
        $this->componentRegistrar = $componentRegistrar;
        $this->cache = $cache;
        $this->directoryReader = $directoryReader;
        $this->kernel = $kernel;
        $this->config = $config;
    }

    public function generateOrmConfig(): Configuration
    {
        $models = $this->cache->get('orm.models.dirs', function () {
            $allGlobs = [];

            foreach ($this->componentRegistrar->getAllRegisteredModules() as $moduleName) {
                $modulePath = $this->directoryReader->getModuleDir($moduleName);
                $glob = $modulePath . DIRECTORY_SEPARATOR . 'EntityModels' . DIRECTORY_SEPARATOR;

                if (is_dir($glob)) {
                    $allGlobs[] = $glob;
                }
            }

            return array_unique($allGlobs);
        });
        $cache = $this->cache instanceof AdapterInterface ? $this->cache : null;

        $config = ORMSetup::createAttributeMetadataConfiguration(
            $models,
            !$this->config->isProduction(),
            $this->kernel->getGeneratedDir(),
            $cache
        );

        return $config;
    }
}
