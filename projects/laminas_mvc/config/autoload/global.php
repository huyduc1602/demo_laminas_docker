<?php

/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\Adapter;

return [
    'service_manager' => [
        'factories' => [
            AdapterInterface::class => function($container) {
                $config = $container->get('config');
                return new Adapter($config['db']);
            },
        ],
    ],
    'db' => [
        'driver' => 'Pdo',
        'dsn'    => 'mysql:dbname=laminas_mvc;host=172.17.0.1',
        'username' => 'root',
        'password' => 'root',
    ],
];
