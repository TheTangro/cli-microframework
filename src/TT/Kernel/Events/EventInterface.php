<?php

namespace TT\Kernel\Events;

interface EventInterface
{
    /**
     * @param string $param
     * @return mixed|null
     */
    public function get(string $param);
}
