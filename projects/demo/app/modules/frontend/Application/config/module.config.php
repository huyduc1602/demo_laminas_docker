<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Laminas\ServiceManager\Factory\InvokableFactory;
use \Zf\Ext\Router\RouterLiteral;
use \Zf\Ext\Router\RouterSegment;

return [
    'router' => [
        'routes' => [
            'home' => [
                'type' => RouterLiteral::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'demo' => [
                'type' => RouterSegment::class,
                'options' => [
                    'route'    => '/demo[/:id]',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'demo',
                        'type'       => 'demo'
                    ],
                ],
            ],
            'test' => [
                'type' => RouterSegment::class,
                'options' => [
                    'route'    => '/test[/:id]',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'demo',
                        'type'       => 'test'
                    ],
                ],
            ],
        ]   
    ],
    'controllers' => [
        'factories' => [
        ],
        'invokables' => [
            Controller\IndexController::class,
            Controller\PostController::class
        ]
    ],
    
    'service_manager' => [
        'factories' => [
            //Service\NavManager::class => Service\Factory\NavManagerFactory::class,
        ],
    ],
    'view_helpers' => [
        'factories' => [
            View\Helper\HeaderSession::class => View\Helper\Factory\HeaderSessionFactory::class,
            View\Helper\BodyRightSession::class => View\Helper\Factory\BodyRightSessionFactory::class,
            View\Helper\FooterSession::class => View\Helper\Factory\FooterSessionFactory::class,
            
            View\Helper\Breadcrumbs::class => InvokableFactory::class,
            View\Helper\Toolbars::class => InvokableFactory::class,
        ],
        'aliases' => [
            'headerSession' => View\Helper\HeaderSession::class,
            'bodyRightSession' => View\Helper\BodyRightSession::class,
            'footerSession' => View\Helper\FooterSession::class,
            
            'pageBreadcrumbs' => View\Helper\Breadcrumbs::class,
            'toolbars' => View\Helper\Toolbars::class,
        ],
    ],
    'view_manager' => [
        'template_map' => [
            'application/partial/paginator' => __DIR__ . '/../view/application/partial/paginator.phtml',
            'application/index/index'    => __DIR__ . '/../view/application/index/index.phtml',
            'application/index/toolbar'  => __DIR__ . '/../view/application/index/toolbar.phtml',
            'application/layout/menu-top'=> __DIR__ . '/../view/application/layout/menu-top.phtml',
            'application/layout/banner'  => __DIR__ . '/../view/application/layout/banner.phtml',
            'application/layout/main-menu'=> __DIR__ . '/../view/application/layout/main-menu.phtml',
            'application/layout/footer' => __DIR__ . '/../view/application/layout/footer.phtml',
            'home/left/menu'            => __DIR__ . '/../view/application/index/_partial/home-page-left-menu.phtml',
            'application/share-social'  => __DIR__ . '/../view/application/index/_partial/share-social.phtml',
            'menu/right'                 => __DIR__ . '/../view/application/layout/menu-right.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ]
];
