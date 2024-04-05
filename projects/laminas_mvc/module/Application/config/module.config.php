<?php

declare(strict_types=1);

namespace Application;

use Application\Controller;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Album\Model\AlbumTableFactory;
use Album\Model\AlbumTable;

return [
    'router' => [
        'routes' => [
            'home' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'hello-world' => [
                'type' => Literal::class, // exact match of URI path
                'options' => [
                    'route' => '/hello/world', // URI path
                    'defaults' => [
                        'controller' => Controller\HelloController::class, // unique name
                        'action'     => 'world',
                    ],
                ],
            ],
            'hello-api' => [
                'type' => Literal::class, // exact match of URI path
                'options' => [
                    'route' => '/hello/api', // URI path
                    'defaults' => [
                        'controller' => Controller\HelloController::class, // unique name
                        'action'     => 'api',
                    ],
                ],
            ],
           'album' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/album[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\AlbumController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'application' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/application[/:action]',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => InvokableFactory::class,
            Controller\HelloController::class => InvokableFactory::class,
            Controller\AlbumController::class => ReflectionBasedAbstractFactory::class,
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
            'application/album/index' => __DIR__ . '/../../Album/view/album/album/index.phtml',
            'application/album/add' => __DIR__ . '/../../Album/view/album/album/add.phtml',
            'application/album/edit' => __DIR__ . '/../../Album/view/album/album/edit.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
            'album' => __DIR__ . '/../../Album/view',
        ],
    ],
    'service_manager' => [
        'factories' => [
            AlbumTable::class => AlbumTableFactory::class,
        ],
    ],
];
