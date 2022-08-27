<?php

namespace TT\Kernel\Factories;

use Monolog\Handler\FilterHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use TT\Kernel\Config;
use TT\Kernel\FactoryInterface;

class SystemLoggerHandlerFactory implements FactoryInterface
{
    public const DEFAULT_LOG_LEVEL = Logger::DEBUG;
    public const DEFAULT_MAX_LOG_LEVEL = Logger::EMERGENCY;

    private HandlerInterface $systemHandler;

    private Config $config;

    public function __construct(
        HandlerInterface $systemHandler,
        Config $config
    ) {
        $this->systemHandler = $systemHandler;
        $this->config = $config;
    }

    public function create(): object
    {
        $productionLogLevel = $this->config->get('production/logging/min_log_level', null);
        $productionMaxLogLevel = $this->config->get('production/logging/max_log_level', null);
        $isProduction = $this->config->get('is_production');
        $minLogLevel = $isProduction && $productionLogLevel ? $productionLogLevel : self::DEFAULT_LOG_LEVEL;
        $maxLogLevel = $isProduction && $productionMaxLogLevel ? $productionMaxLogLevel : self::DEFAULT_MAX_LOG_LEVEL;
        $handler = new FilterHandler($this->systemHandler, $minLogLevel, $maxLogLevel);

        return $handler;
    }
}
