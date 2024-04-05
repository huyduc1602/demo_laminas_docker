<?php

/**
 * Development-only configuration.
 *
 * Put settings you want enabled when under development mode in this file, and
 * check it into your repository.
 *
 * Developers on your team will then automatically enable them by calling on
 * `composer development-enable`.
 */

declare(strict_types=1);

use Mezzio\Container;
use Mezzio\Middleware\ErrorResponseGenerator;

set_error_handler(function($severity, $message, $file, $line) {
    if (error_reporting() & $severity) {
        fwrite(STDOUT, "\nSwool debug:\nError: {$message}\nAt line: {$line} of file: {$file}\n");
        var_dump(
            debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)
        );
        fwrite(STDOUT, "End swool debug\n\n");
    }
});
return [
    'dependencies' => [
        'factories' => [
            ErrorResponseGenerator::class => Container\WhoopsErrorResponseGeneratorFactory::class,
            'Mezzio\Whoops'               => Container\WhoopsFactory::class,
            'Mezzio\WhoopsPageHandler'    => Container\WhoopsPageHandlerFactory::class,
        ],
    ],
    'whoops'       => [
        'json_exceptions' => [
            'display'    => true,
            'show_trace' => true,
            'ajax_only'  => true,
        ],
    ],
];
