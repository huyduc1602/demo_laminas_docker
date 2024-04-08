<?php
namespace UploadFile;

use GrootSwoole\HandlerMiddleware\EventTriggerDispatcherHandlerMiddleware as DefaultHandler;
use GrootSwoole\MiddlewareFactory\TaskEventListenerFactory;
use Mezzio\Swoole\Task\DeferredServiceListenerDelegator;
use UploadFile\Task\{
    TestUploadTask, TestUploadListener
};

return [
    'dependencies' => [
        'invokables' => [
        ],

        'factories' => [
            TestUploadListener::class => TaskEventListenerFactory::class
        ],

        'aliases' => [
        ],

        'delegators' => [
            TestUploadListener::class => [
                DeferredServiceListenerDelegator::class
            ],
        ]
    ],
    'mezzio-swoole' => [
        'swoole-http-server' => [
            'listeners' => [
                TestUploadTask::class => [
                    TestUploadListener::class,
                ],
            ],
        ],
    ],
    'routes' => [
        'api.test-upload' => [
            'path' => '/api/test-upload',
            'middleware'=> DefaultHandler::class,
            'method'    => ['POST','GET'],
            'task'      => TestUploadTask::class,
            'params'    => [
                'file' => '',
                'size'  => ''
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