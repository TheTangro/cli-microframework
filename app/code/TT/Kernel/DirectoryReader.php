<?php

namespace TT\Kernel;

use TT\Kernel\Exceptions\AppException;

class DirectoryReader
{
    private ComponentRegistrar $componentRegistrar;

    public function __construct(
        ComponentRegistrar $componentRegistrar
    ) {
        $this->componentRegistrar = $componentRegistrar;
    }

    /**
     * @param string $moduleName
     *
     * @return string
     *
     * @throws AppException
     */
    public function getModuleDir(string $moduleName): string
    {
        $dir = $this->componentRegistrar->getModulePath($moduleName);

        if ($dir) {
            return $dir;
        }

        throw new AppException('Module not found');
    }

    public function getFilePath($file, array $dirsInPath = []): string
    {
        $dirs = $dirsInPath ? DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $dirsInPath) : '';

        return $this->getRootDir() . $dirs . DIRECTORY_SEPARATOR . $file;
    }

    public function getRootDir(): string
    {
        return BP;
    }
}
