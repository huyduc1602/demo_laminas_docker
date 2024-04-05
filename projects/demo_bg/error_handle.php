<?php
if (function_exists('Sentry\init'))
    \Sentry\init([
        'dsn' => 'https://cb073be0bac6461da3c38d969da56d6c@sentry.g-root.com/4',
        'environment' => 'vtest'
    ]);

function proccessLogError ($type, $message, $file, $line) {
    if (error_reporting() & $type) {

        $now = date('Y/m/d H:i:s');
        $error = str_replace(
            "'", '',
            "[{$now}] Problem:\n{$message}\nAt line: {$line} of file: {$file}"
        );

        echo "\n\nDebug: {$error}\n\n";
        if (function_exists('Sentry\captureMessage'))
            Sentry\captureMessage($error, Sentry\Severity::warning());
    }
}

function checkShutdownError (){
    $error = error_get_last();

    if ( is_array($error) ){
        call_user_func_array('proccessLogError', $error);
    }
}

set_error_handler('proccessLogError');

// register_shutdown_function('checkShutdownError');
?>