<?php

namespace TT\Kernel\Events;

use Psr\Container\ContainerInterface;
use TT\Kernel\Exceptions\InvalidConfigException;

class EventManager
{
    private array $subscribers;

    private ContainerInterface $container;

    public function __construct(
        ContainerInterface $container,
        array $subscribers = []
    ) {
        $this->subscribers = $subscribers;
        $this->validateSubscribers($subscribers);
        $this->container = $container;
    }

    public function dispatchEvent(string $eventName, array $data): void
    {
        $event = new Event($eventName, $data);
        $subscribers = array_map([$this->container, 'get'], $this->subscribers[$eventName] ?? []);

        /** @var SubscriberInterface $subscriber **/
        foreach ($subscribers as $subscriber) {
            $subscriber->onEvent($event);

            if ($event->isStopProcessing()) {
                break;
            }
        }
    }

    /**
     * @param array $subscribers
     *
     * @return void
     *
     * @throws InvalidConfigException
     */
    private function validateSubscribers(array $subscribers): void
    {
        $subscribers = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($subscribers));

        foreach ($subscribers as $subscriber) {
            if (!is_a($subscriber, SubscriberInterface::class, true)) {
                throw new InvalidConfigException("Invalid subscriber {$subscriber}");
            }
        }
    }
}
