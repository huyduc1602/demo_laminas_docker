<?php 
function proccessLogError ($severity, $message, $file, $line) {
    if (error_reporting() & $severity) {
        
        $now = date('Y/m/d H:i:s');
        $env = 'vtest';
        $file = str_replace(realpath( __DIR__ . '/../'), '', $file);
        $error = str_replace(
            "'", '',
            "[{$now} - {$env}] Error:\n{$message}\nAt line: {$line} of file: {$file}"
        );
        
        echo "<pre>Debug: {$error}</pre>";
    }
}

function checkShutdownError (){
    $error = error_get_last();
    if ( is_array($error) ){
        call_user_func_array('proccessLogError', $error);
    }
}

set_error_handler('proccessLogError');

register_shutdown_function('checkShutdownError');
?>