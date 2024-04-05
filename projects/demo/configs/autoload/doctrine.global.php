<?php
$dbConfigs = require CONFIG_PATH. '/db.config.php';
return [
    'doctrine' => [
        'connection' => [
            //Config default
            'orm_default' => [
                'driverClass' => 'Doctrine\DBAL\Driver\PDO\MySQL\Driver',
                'params' => $dbConfigs[APPLICATION_VERSION]
            ],
            // more connection after here
//             'orm_relay' => [
//                 'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
//                 'params' => $dbReplayConfigs
//             ],
        ],
        
        'configuration' => [
            'orm_default' => [
                'metadata_cache'    => 'redis',
                'query_cache'       => 'redis',
                'result_cache'      => 'redis',
                'hydration_cache'   => 'redis',
                
                'driver'            => 'orm_default',
                'generate_proxies'  => true,
                'proxy_dir'         => DATA_PATH. '/DT_Proxies',
                'proxy_namespace'   => 'Proxies',
                'datetime_functions' => [
                    'FROM_UNIXTIME' => 'DoctrineExtensions\Query\Mysql\FromUnixtime',
                    'UNIX_TIMESTAMP' => 'DoctrineExtensions\Query\Mysql\UnixTimestamp'
                ],
                'numeric_functions' => [
                    'RAND'       => 'DoctrineExtensions\Query\Mysql\Rand',
                    'ROUND'      => 'DoctrineExtensions\Query\Mysql\Round',
                ],
                'string_functions'  => [
                    'REPLACE'           => 'DoctrineExtensions\Query\Mysql\Replace',
                    'STR_TO_DATE'       => 'DoctrineExtensions\Query\Mysql\StrToDate',
                    'SUBSTRING_INDEX'   => 'DoctrineExtensions\Query\Mysql\SubstringIndex',
                    'GROUP_CONCAT'      => 'DoctrineExtensions\Query\Mysql\GroupConcat',
                    'CONCAT_WS'      	=> 'DoctrineExtensions\Query\Mysql\ConcatWs',
                    'IFNULL'            => 'DoctrineExtensions\Query\Mysql\IfNull',
                    'IFELSE'            => 'DoctrineExtensions\Query\Mysql\IfElse',
                    'FIELD'             => 'DoctrineExtensions\Query\Mysql\Field',
                    'MATCH'             => 'Zf\Ext\Model\MatchAgainstFull',
                    'JSON_EXTRACT'      => 'Zf\Ext\Model\JsonExtract',
                    'JSON_SEARCH'       => 'Zf\Ext\Model\JsonSearch',
                ]
            ],
        ],
        
        'driver' => [
            'zf_dt_driver' => [
                'class'             => \Doctrine\Persistence\Mapping\Driver\PHPDriver::class,
                'cache'             => 'redis',
                'generate_proxies'  => true,
                'proxy_dir'         => DATA_PATH. '/DT_Proxies',
                'proxy_namespace'   => 'Proxies',
                'paths' => [
                    APPLICATION_PATH . '/../Models',
                    LIBRARY_PATH
                ],
            ],
            
            'orm_default' => [
                'class' => \Doctrine\ORM\Mapping\Driver\AnnotationDriver::class,
                'cache'             => 'redis',
                'drivers' => [
                    'Models\Entities'    => 'zf_dt_driver',
                    'Models\Repositories'=> 'zf_dt_driver',
                    'Models\Utilities'   => 'zf_dt_driver',
                    'DoctrineExtensions' => 'zf_dt_driver',
                ]
            ],
            
            /* 'logs'       => [
                'path'          => DATA_PATH .' /../logs/queries',
                'rotate_pattern'=> 'Y-m-d-H'
            ], */
        ],
        'cache' => [
            'redis' => [
                'instance' => 'doctrine.cache.redis'
            ]
        ],
        'entitymanager' => [
            'orm_default' => [
                'connection'    => 'orm_default',
                'configuration' => 'orm_default',
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            'doctrine.cache.redis' => \Zf\Ext\Model\DoctrineCacheRedisFactory::class,
        ],
    ],
];
