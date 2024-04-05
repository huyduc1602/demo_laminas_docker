<?php
namespace Notify;

use Mezzio\Swoole\Task\DeferredServiceListenerDelegator;

use GrootSwoole\MiddlewareFactory\TaskEventListenerFactory;
use GrootSwoole\HandlerMiddleware\EventTriggerDispatcherHandlerMiddleware as DefaultHandler;

use Notify\Task\{
    TestMailTask, TestMailTaskListener
};

return [
    'dependencies' => [
        'invokables' => [
        ],

        'factories' => [
            TestMailTaskListener::class => TaskEventListenerFactory::class
        ],

        'aliases' => [
        ],

        'delegators' => [
            TestMailTaskListener::class => [
                DeferredServiceListenerDelegator::class
            ],
        ]
    ],
    'mezzio-swoole' => [
        'swoole-http-server' => [
            'listeners' => [
                TestMailTask::class => [
                    TestMailTaskListener::class,
                ],
            ],
        ],
    ],
    'routes' => [
        'api.test-mail' => [
            'path' => '/api/test-mail',
            'middleware'=> DefaultHandler::class,
            'method'    => ['POST','GET'],
            'task'      => TestMailTask::class,
            'params'    => [
                'email' => '',
                'name'  => ''
            ]
        ],
    ],
    'templates' => [
        'paths' => [
            'error' => ['templates/error'],
            'layout' => ['templates/layout'],
        ],
    ],
];
?>