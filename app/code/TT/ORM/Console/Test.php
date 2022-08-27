<?php

namespace TT\ORM\Console;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Test extends Command
{
    private \TT\ORM\Model\ResourceConnection $resourceConnection;

    public function __construct(
        \TT\ORM\Model\ResourceConnection $resourceConnection,
        string $name = null
    ) {
        parent::__construct($name);
        $this->setName('test:test');
        $this->resourceConnection = $resourceConnection;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $d = $this->resourceConnection->getEntityManager();

        return 0;
    }
}
