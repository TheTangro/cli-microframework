<?php

namespace TT\Kernel\Logging\Handlers;

use Monolog\Handler\StreamHandler as StreamHandler;
use Monolog\Logger;
use TT\Kernel\DirectoryReader;

class FileHandler extends StreamHandler
{
    public function __construct(
        DirectoryReader $directoryReader,
        $stream,
        $level = Logger::DEBUG,
        bool $bubble = true,
        ?int $filePermission = null,
        bool $useLocking = false
    ) {
        if (is_string($stream)) {
            $stream = $directoryReader->getRootDir() . DIRECTORY_SEPARATOR . ltrim($stream, '/');
        }

        parent::__construct($stream, $level, $bubble, $filePermission, $useLocking);
    }
}
