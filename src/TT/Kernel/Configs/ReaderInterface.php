<?php

namespace TT\Kernel\Configs;

interface ReaderInterface
{
    public function read(): array;
}