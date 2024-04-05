<?php

$dbConfigs = require './config/db.config.php';

return [
    'doctrine' => [
        'connection' => [
            // Config default
            'orm_default' => [
                'driverClass' => \Doctrine\DBAL\Driver\PDO\MySQL\Driver::class,
                'params' => $dbConfigs[APPLICATION_VERSION]
            ]
            // more connection after here
            // 'orm_relay' => [
            // 'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
            // 'params' => $dbReplayConfigs
            // ],
        ],

        'configuration' => [
            'orm_default' => [
                'metadata_cache' => 'redis',
                'query_cache' => 'redis',
                'result_cache' => 'redis',
                'hydration_cache' => 'redis',

                'driver' => 'orm_default',
                'generate_proxies' => true,
                'proxy_dir' => DATA_PATH . '/DT_Proxies',
                'proxy_namespace' => 'Proxies',
                'datetime_functions' => [
                    'FROM_UNIXTIME' => 'DoctrineExtensions\Query\Mysql\FromUnixtime',
                    'UNIX_TIMESTAMP' => 'DoctrineExtensions\Query\Mysql\UnixTimestamp'
                ],
                'numeric_functions' => [
                    'RAND' => 'DoctrineExtensions\Query\Mysql\Rand',
                    'ROUND' => 'DoctrineExtensions\Query\Mysql\Round'
                ],
                'string_functions' => [
                    'REPLACE' => 'DoctrineExtensions\Query\Mysql\Replace',
                    'STR_TO_DATE' => 'DoctrineExtensions\Query\Mysql\StrToDate',
                    'SUBSTRING_INDEX' => 'DoctrineExtensions\Query\Mysql\SubstringIndex',
                    'GROUP_CONCAT' => 'DoctrineExtensions\Query\Mysql\GroupConcat',
                    'CONCAT_WS' => 'DoctrineExtensions\Query\Mysql\ConcatWs',
                    'IFNULL' => 'DoctrineExtensions\Query\Mysql\IfNull',
                    'IFELSE' => 'DoctrineExtensions\Query\Mysql\IfElse',
                    'REGEXP'            => 'DoctrineExtensions\Query\Mysql\Regexp',
                    'MATCH' => 'Zf\Ext\Model\MatchAgainstFull',
                    'JSON_EXTRACT' => 'Zf\Ext\Model\JsonExtract',
                    'JSON_OBJECT' => 'Zf\Ext\Model\JsonObject',
                    'REGEXP'      => 'DoctrineExtensions\Query\Mysql\Regexp',
                ]
            ]
        ],

        'driver' => [
            'zf_dt_driver' => [
                'class'             => \Doctrine\Persistence\Mapping\Driver\PHPDriver::class,
                'cache'             => 'redis',
                'generate_proxies'  => true,
                'proxy_dir'         => DATA_PATH . '/DT_Proxies',
                'proxy_namespace'   => 'Proxies',
                'redis_cache'       => REDIS_CONFIG,
                'paths' => [
                    DATA_PATH . '/../app/Models',
                    LIBRARY_PATH
                ]
            ],

            'orm_default' => [
                'class' => \Doctrine\ORM\Mapping\Driver\AnnotationDriver::class,
                'cache'             => 'redis',
                'drivers' => [
                    'Models\Entities' => 'zf_dt_driver',
                    'Models\Repositories' => 'zf_dt_driver',
                    'Models\Utilities' => 'zf_dt_driver',
                    'DoctrineExtensions' => 'zf_dt_driver'
                ]
            ]
            // 'logs' => [
            // 'path' => DATA_PATH .' /../logs/doctrine_queries',
            // 'rotate_pattern'=> 'Y-m-d-H'
            // ],
        ],
        'cache' => [
            'redis' => [
                'instance' => 'doctrine.cache.redis'
            ]
        ],
        'entitymanager' => [
            'orm_default' => [
                'connection' => 'orm_default',
                'configuration' => 'orm_default'
            ]
        ]
    ],
    'dependencies' => [
        'factories' => [
            'doctrine.cache.redis' => \GrootSwoole\DoctrineCacheRedisFactory::class
        ]
    ]
];
