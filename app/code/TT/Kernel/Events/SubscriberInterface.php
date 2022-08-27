<?php

namespace TT\Kernel\Events;

interface SubscriberInterface
{
    public function onEvent(EventInterface $event);
}