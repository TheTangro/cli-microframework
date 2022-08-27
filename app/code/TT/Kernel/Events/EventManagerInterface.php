<?php

namespace TT\Kernel\Events;

interface EventManagerInterface
{
    public function dispatchEvent(string $eventName, array $data): void;
}