<?php

namespace TT\Kernel\Events;

class Event implements EventInterface
{
    private array $data;

    private string $name;

    private bool $stopProcessing = false;

    public function __construct(
        string $name,
        array $data
    ) {
        $this->data = $data;
        $this->name = $name;
    }

    public function get(string $param)
    {
        return $this->data[$param] ?? null;
    }

    public function set(string $param, $value): void
    {
        $this->data[$param] = $value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isStopProcessing(?bool $isStopProcessing = null): bool
    {
        if ($isStopProcessing !== null) {
            $this->stopProcessing = $isStopProcessing;
        }

        return $this->stopProcessing;
    }
}
