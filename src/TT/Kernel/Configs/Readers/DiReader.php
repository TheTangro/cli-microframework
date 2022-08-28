<?php

namespace TT\Kernel\Configs\Readers;

use DOMDocument;
use Symfony\Contracts\Cache\CacheInterface;
use TT\Kernel\ComponentRegistrar;
use TT\Kernel\DirectoryReader;
use TT\Kernel\Exceptions\InvalidConfigException;
use TT\Kernel\FactoryInterface;
use function Di\autowire;
use function DI\factory;

class DiReader
{
    private DirectoryReader $directoryReader;

    private CacheInterface $cache;

    private ComponentRegistrar $componentRegistrar;

    public function __construct(
        DirectoryReader $directoryReader,
        CacheInterface $cache,
        ComponentRegistrar $componentRegistrar
    ) {
        $this->directoryReader = $directoryReader;
        $this->cache = $cache;
        $this->componentRegistrar = $componentRegistrar;
    }

    public function read(): array
    {
        return $this->cache->get('kernel.di.config', \Closure::fromCallable([$this, 'readFiles']));
    }

    /**
     * @param DOMDocument $document
     * @param array $result
     * 
     * @throws InvalidConfigException
     */
    private function parseFactories(DOMDocument $document, array &$result): void
    {
        $factories = $document->getElementsByTagName('factory');

        foreach ($factories as $factory) {
            $reference = $factory->getAttribute('reference');
            [$class, $method] = explode('::', $reference);

            if (!class_exists($class) || !is_a($class, FactoryInterface::class, true)) {
                throw new InvalidConfigException("Invalid reference {$reference}");
            }

            $result[$factory->getAttribute('for')] = factory([$class, $method]);
        }
    }

    /**
     * @param DOMDocument $document
     * @param array $result
     */
    private function parseDiType(DOMDocument $document, bool $virtual, array &$result): void
    {
        $typeName = $virtual ? 'virtualType' : 'type';
        $types = $document->getElementsByTagName($typeName);

        foreach ($types as $type) {
            $arguments = $type->getElementsByTagName('arguments')[0]->getElementsByTagName('argument');
            $compiledArguments = [];

            foreach ($arguments as $argument) {
                $name = $argument->getAttribute('name');
                $argumentType = $argument->getAttribute('type');
                $value = trim($argument->textContent);

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

                $value = $argumentType === 'object'
                    ? $this->getAiutowire(trim($value, '\\'))
                    : $this->castType($argumentType, $value);
                $compiledArguments[$name] = $value;
            }

            $result[$type->getAttribute('name')] = [
                'arguments' => $compiledArguments,
                'type' => $virtual ? $type->getAttribute('type') : $type->getAttribute('name')
            ];
        }
    }

    private function castType(String $type, $value)
    {
        switch ($type) {
            case 'int':
                return (int) $value;
            case 'string':
                return (string) $value;
            case 'float':
            case 'double':
                return (float) $value;
            case 'null':
                return null;
            case 'const':
                return constant($value);
            case 'bool':
            case 'boolean':
                return strtolower(trim($value)) === 'true';
            case 'array':
                return (array) $value;
            default:
                throw new InvalidConfigException("Invalid type {$type}");
        }
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
                $arguments = $configNode['arguments'];
                $type = $configNode['type'];
                $type = is_string($type) ? $type : reset($type);
                $autowire = autowire($type);

                foreach ($arguments as $paramName => $paramValue) {
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

        $this->parseFactories($document, $result);
        $this->parseDiType($document, false, $result);
        $this->parseDiType($document, true, $result);
        $this->parsePreferences($document, $result);

        return $result;
    }

    private function parseItem(\DOMNode $DOMNode)
    {
        $itemName = $DOMNode->getAttribute('name');
        $itemType = $DOMNode->getAttribute('type');
        $itemValue = trim($DOMNode->textContent);

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
            $itemValue = $itemType === 'object' ? $this->getAiutowire(trim($itemValue, '\\')) : $itemValue;
        }

        return $itemValue;
    }

    private function getAiutowire(string $type)
    {
        return factory('TT\Kernel\Factories\PreferenceFactory::create')->parameter('type', $type);
    }

    private function getDiFiles(): array
    {
        return $this->cache->get(
            'kernel.di.files',
            function () {
                $allFiles = glob($this->directoryReader->getRootDir() . '/*/etc/di.xml');

                foreach ($this->componentRegistrar->getAllRegisteredModules() as $module) {
                    $glob = $this->directoryReader->getModuleDir($module) . '/etc/di.xml';
                    $allFiles = array_merge($allFiles, glob($glob));
                }

                return array_unique($allFiles);
            }
        );
    }

    private function parsePreferences(DOMDocument $document, array &$result): void
    {
        $preferences = $document->getElementsByTagName('preference');

        foreach ($preferences as $preference) {
            $result[$preference->getAttribute('for')] = $this->getAiutowire($preference->getAttribute('type'));
        }
    }
}
