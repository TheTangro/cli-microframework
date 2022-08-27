<?php

namespace TT\ORM\Model;

use TT\Kernel\Config;

class DBConfigs
{
    private Config $config;

    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    public function getConnectionParams(): array
    {
        $isProduction = $this->config->isProduction() && !$this->config->get('force_skip_env');

        return [
            'driver' => $isProduction ? getenv('DB_DRIVER') : $this->config->get('env/db_driver'),
            'user' => $isProduction ? getenv('DB_USER') : $this->config->get('env/db_user'),
            'password' => $isProduction ? getenv('DB_PASSWORD') : $this->config->get('env/db_password'),
            'host' => $isProduction ? getenv('DB_HOST') : $this->config->get('env/db_host'),
            'port' => $isProduction ? getenv('DB_PORT') : $this->config->get('env/db_port'),
            'dbname' => $isProduction ? getenv('DB_NAME') : $this->config->get('env/db_name'),
            'charset' => $isProduction ? getenv('DB_CHARSER') : $this->config->get(
                'env/db_charset',
                'utf8'
            ),
        ];
    }
}
