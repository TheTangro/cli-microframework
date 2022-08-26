<?php

namespace TT\Kernel;

use TT\Kernel\Exceptions\AppException;

class DirectoryReader
{
    const DIRECTORIES = [
        'app/code'
    ];

    /**
     * @param string $moduleName
     *
     * @return string
     *
     * @throws AppException
     */
    public function getModuleDir(string $moduleName): string
    {
        foreach (self::DIRECTORIES as $directory) {
            $modulePath = str_replace('_', DIRECTORY_SEPARATOR, $moduleName);
            $dir = BP . DIRECTORY_SEPARATOR . $directory . DIRECTORY_SEPARATOR . $modulePath;

            if (is_dir($dir)) {
                return $dir;
            }
        }

        throw new AppException('Module not found');
    }

    public function getFilePath($file, array $dirsInPath = []): string
    {
        $dirs = $dirsInPath ? DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $dirsInPath) : '';

        return BP . $dirs . DIRECTORY_SEPARATOR . $file;
    }
}
