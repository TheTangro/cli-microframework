<?php

namespace TT\Kernel\Configs;

use Psr\Container\ContainerInterface;
use TT\Kernel\Exceptions\AppException;

class Reader
{
    private ContainerInterface $container;

    public function __construct(
        \Di\Container $container
    ) {
        $this->container = $container;
    }

    public function read(string $name): array
    {
        $class = sprintf('%ss\%sReader', __CLASS__, ucfirst($name));

        if (class_exists($class)) {
            $reader = $this->container->get($class);

            return $reader->read();
        }

        throw new AppException('Invalid config name');
    }
}
