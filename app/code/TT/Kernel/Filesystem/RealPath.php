<?php

namespace TT\Kernel\Filesystem;

use TT\Kernel\DirectoryReader;

class RealPath
{
    private DirectoryReader $directoryReader;
    
    public function __construct(
        DirectoryReader $directoryReader
    ) {
        $this->directoryReader = $directoryReader;
    }
    
    public function get(string $path): string
    {
        if (strpos(DIRECTORY_SEPARATOR, $path) !== 0) {
            $path = sprintf(
                '%s%s%s%s',
                rtrim($this->directoryReader->getRootDir(), '/'),
                DIRECTORY_SEPARATOR,
                $path,
                DIRECTORY_SEPARATOR
            );
        }
        
        return $path;
    }
}
