<?php

namespace TT\ORM\Model;

use Doctrine\ORM\EntityManager;
use TT\ORM\Model\Connection\ConfigLoader;

class ResourceConnection
{
    private ?EntityManager $entityManager = null;

    private array $params;

    private ConfigLoader $configLoader;

    public function __construct(
        array $params,
        ConfigLoader $configLoader
    ) {
        $this->params = $params;
        $this->configLoader = $configLoader;
    }

    public function getEntityManager(): EntityManager
    {
        if ($this->entityManager === null) {
            $this->entityManager = $this->createEntityManager();
        }

        return $this->entityManager;
    }

    private function createEntityManager(): EntityManager
    {
        return \Doctrine\ORM\EntityManager::create(
            $this->params,
            $this->configLoader->generateOrmConfig()
        );
    }

    public function reset(): void
    {
        unset($this->entityManager);
        $this->entityManager = null;
    }
}
