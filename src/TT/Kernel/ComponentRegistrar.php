<?php

namespace TT\Kernel;

class ComponentRegistrar
{
    public const TYPE_MODULE = 'module';

    private static array $components = [];

    public static function register(string $name, string $type, string $directory): void
    {
        self::$components[$type][$name] = $directory;
    }

    public function getModulePath(string $name): ?string
    {
        return self::$components[self::TYPE_MODULE][$name] ?? null;
    }

    public function getAllRegisteredModules(): array
    {
        return array_keys(self::$components[self::TYPE_MODULE] ?? []);
    }
}
