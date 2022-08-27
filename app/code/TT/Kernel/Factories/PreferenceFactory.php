<?php

namespace TT\Kernel\Factories;

use Psr\Container\ContainerInterface;

class PreferenceFactory
{
    private ContainerInterface $container;

    public function __construct(
        ContainerInterface $container
    ) {
        $this->container = $container;
    }

    public function create(string $type): object
    {
        return $this->container->get($type);
    }
}
