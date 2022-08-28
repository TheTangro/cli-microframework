<?php

namespace TT\ORM\Observers\Kernel\Init;

use Psr\Log\LoggerInterface;
use TT\Kernel\Events\EventInterface;
use TT\Kernel\Events\SubscriberInterface;
use TT\ORM\Model\DBConfigs;
use TT\ORM\Model\ResourceConnection;

class LoadConfig implements SubscriberInterface
{
    private DBConfigs $DBConfigs;

    public function __construct(
        LoggerInterface $logger,
        DBConfigs $DBConfigs
    ) {
        $this->DBConfigs = $DBConfigs;
    }

    public function onEvent(EventInterface $event)
    {
        $additionalConfigs = $event->get('additional_definitions');
        $additionalConfigs[ResourceConnection::class] = \Di\autowire(ResourceConnection::class)
            ->constructorParameter('params', $this->DBConfigs->getConnectionParams());
        $event->set('additional_definitions', $additionalConfigs);
    }
}
