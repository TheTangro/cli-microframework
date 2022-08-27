<?php

return [
    'is_production' => true,
    'force_skip_env' => true,
    'production' => [
        'logging' => [
            'min_log_level' => \Monolog\Logger::DEBUG,
            'max_log_level' => \Monolog\Logger::NOTICE
        ]
    ],
    'modules' => [
        'TT_Kernel' => true
    ]
];
