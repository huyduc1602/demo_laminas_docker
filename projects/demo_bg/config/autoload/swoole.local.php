<?php 
//use Mezzio\Swoole\Event;
//use App\Logger\LoggingListener;
//use App\Logger\TaskCompletionLoggingListener;

use Mezzio\Swoole\Event\TaskEvent;
use Mezzio\Swoole\Task\TaskInvokerListener;

use GrootSwoole\MiddlewareFactory\EventTriggerDispatcherHandlerFactory;
use GrootSwoole\HandlerMiddleware\EventTriggerDispatcherHandlerMiddleware;

return [
    'dependencies' => [
        'invokables' => [
        ],
        'factories' => [
            EventTriggerDispatcherHandlerMiddleware::class => EventTriggerDispatcherHandlerFactory::class,
        ]
    ],
    'mezzio-swoole' => [
        /* 'hot-code-reload' => [
            // Time in milliseconds between checks to changes in files.
            'interval' => 5000,
            'paths'    => [
                // List of paths, either files or directories, to scan for changes.
                // By default this is empty; you will need to configure it.
                // A common value:
                "{$path}/config",
                "{$path}/app",
            ],
        ], */
        
        'enable_coroutine' => true,
        'swoole-http-server' => [
			'hook_flags'    => SWOOLE_HOOK_ALL,
            'host'          => 'api.demo.local',
            'port'          => 8080,
            'mode'          => SWOOLE_BASE,
            'protocol'      => SWOOLE_SOCK_TCP | SWOOLE_SSL, // SSL-enable the server
            'process-name'  => 'demo',
            'user'              => 'www-data',
            'group'             => 'www-data',
            'options'       => [
                'user'              => 'www-data',
                'group'             => 'www-data',
				'hook_flags'        => SWOOLE_HOOK_ALL,
                'worker_num'        => 2, // The number of HTTP Server Workers
                'task_worker_num'   => 2, // swoole_cpu_num(), // The number of Task Workers
                'task_enable_coroutine' => true, // optional to turn on task coroutine support
                //'document_root'         => PUBLIC_PATH,
                // protocol
                'open_http2_protocol'   => true,

                'ssl_cert_file'         => '/etc/ssl/laminas.local.crt',
                'ssl_key_file'          => '/etc/ssl/laminas.local.key',
                
                'ssl_allow_self_signed' => true,
                'ssl_verify_peer'       => false,
                
               'ssl_ciphers'            => 'ALL:!ADH:!EXPORT56:RC4+RSA:+HIGH:+MEDIUM:+LOW:+SSLv3:+EXP',
               'ssl_protocols'          => 0, // added from v4.5.4
            ],
            'listeners' => [
                //Default Task Event
                TaskEvent::class => [
                    TaskInvokerListener::class,
                ],
            ],
        ],
    ],
];
?>