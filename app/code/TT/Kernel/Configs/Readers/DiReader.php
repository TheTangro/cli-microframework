<?php

namespace TT\Kernel\Configs\Readers;

use DOMDocument;
use Symfony\Contracts\Cache\CacheInterface;
use TT\Kernel\DirectoryReader;
use TT\Kernel\Exceptions\InvalidConfigException;
use TT\Kernel\FactoryInterface;

class DiReader
{
    private DirectoryReader $directoryReader;

    private CacheInterface $cache;

    public function __construct(
        DirectoryReader $directoryReader,
        CacheInterface $cache
    ) {
        $this->directoryReader = $directoryReader;
        $this->cache = $cache;
    }

    public function read(): array
    {
        return $this->cache->get('kernel.di.config', \Closure::fromCallable([$this, 'readFiles']));
    }

    private function readFiles(): array
    {
        $moduleDir = $this->directoryReader->getModuleDir('TT_Kernel');
        $xsd = $moduleDir . DIRECTORY_SEPARATOR . 'Configs/schemas' . DIRECTORY_SEPARATOR . 'di.xsd';
        $computedConfig = [];

        foreach ($this->getDiFiles() as $diFile) {
            $xml = new \DOMDocument;
            $xml->load($diFile);

            if (!$xml->schemaValidate($xsd)) {
                throw new InvalidConfigException("Invalid config {$diFile}");
            }

            $computedConfig = array_merge_recursive($computedConfig, $this->parseConfigFile($xml));
        }

        $compiledConfig = $this->compileConfig($computedConfig);

        return $compiledConfig;
    }

    private function compileConfig(array $config): array
    {
        foreach ($config as $key => &$configNode) {
            if (is_array($configNode)) {
                $autowire = \Di\autowire($key);

                foreach ($configNode as $paramName => $paramValue) {
                    $autowire->constructorParameter($paramName, $paramValue);
                }

                $configNode = $autowire;
            }
        }

        return $config;
    }

    private function parseConfigFile(DOMDocument $document): array
    {
        $result = [];
        $preferences = $document->getElementsByTagName('preference');

        foreach ($preferences as $preference) {
            $result[$preference->getAttribute('for')] = $preference->getAttribute('class');
        }

        $factories = $document->getElementsByTagName('factory');

        foreach ($factories as $factory) {
            $reference =  $factory->getAttribute('reference');
            [$class, $method] = explode('::', $reference);

            if (!class_exists($class) || !is_a($class, FactoryInterface::class, true)) {
                throw new InvalidConfigException("Invalid reference {$reference}");
            }

            $result[$factory->getAttribute('for')] = \DI\factory($reference);
        }

        $types = $document->getElementsByTagName('type');

        foreach ($types as $type) {
            $arguments = $type->getElementsByTagName('arguments')[0]->getElementsByTagName('argument');
            $compiledArguments = [];

            foreach ($arguments as $argument) {
                $name = $argument->getAttribute('name');
                $argumentType = $argument->getAttribute('type');
                $value = $argument->getAttribute('value');

                if ($argumentType === 'array') {
                    $innerItems = array_filter(
                        iterator_to_array($argument->childNodes),
                        fn($el) => $el instanceof \DOMElement
                    );

                    $value = [];

                    foreach ($innerItems as $innerItem) {
                        $value[$innerItem->getAttribute('name')] = $this->parseItem($innerItem);
                    }
                }

                $value = $argumentType === 'object' ? \Di\autowire(trim($value, '\\')) : $value;
                $compiledArguments[$name] = $value;
            }

            $result[$type->getAttribute('class')] = $compiledArguments;
        }

        return $result;
    }

    private function parseItem(\DOMNode $DOMNode)
    {
        $itemName = $DOMNode->getAttribute('name');
        $itemType = $DOMNode->getAttribute('type');
        $itemValue = $DOMNode->getAttribute('value');

        if ($itemType === 'array') {
            $innerItems = $innerItems = array_filter(
                iterator_to_array($DOMNode->childNodes),
                fn($el) => $el instanceof \DOMElement
            );
            $itemValue = [];

            foreach ($innerItems as $innerItem) {
                $itemValue[$innerItem->getAttribute('name')] = $this->parseItem($innerItem);
            }
        } else {
            $itemValue = $itemType === 'object' ? \Di\autowire(trim($itemValue, '\\')) : $itemValue;
        }

        return $itemValue;
    }

    private function getDiFiles(): array
    {
        return $this->cache->get(
            'kernel.di.files',
            static fn () => glob(BP . '/*/etc/di.xml')
        );
    }
}
