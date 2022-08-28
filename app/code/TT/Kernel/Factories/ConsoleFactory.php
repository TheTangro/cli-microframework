<?php

namespace TT\Kernel\Factories;

use Symfony\Component\Console\Application;
use TT\Kernel\FactoryInterface;

class ConsoleFactory implements FactoryInterface
{
    private array $commands;

    public function __construct(
        array $commands = []
    ) {
        $this->commands = $commands;
    }

    public function create(): object
    {
        $app = new Application;

        foreach ($this->commands as $command) {
            $app->add($command);
        }

        return $app;
    }
}
