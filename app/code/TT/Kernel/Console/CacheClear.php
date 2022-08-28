<?php

namespace TT\Kernel\Console;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Cache\CacheInterface;

class CacheClear extends Command
{
    protected static $defaultName = 'cache:clear';

    protected static $defaultDescription = 'Clears application cache';

    private CacheInterface $cache;

    public function __construct(
        CacheInterface $cache,
        string $name = null
    ) {
        parent::__construct($name);

        $this->cache = $cache;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->cache->clear();
        $output->writeln('<info>Cache cleared successfuly</info>');

        return 0;
    }
}
