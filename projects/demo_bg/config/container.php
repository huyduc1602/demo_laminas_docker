<?php

declare(strict_types=1);
use Laminas\ServiceManager\ServiceManager;

error_reporting(E_ALL & ~ E_NOTICE & ~ E_DEPRECATED);

ini_set('default_charset', 'utf-8');
date_default_timezone_set ( "Asia/Ho_Chi_Minh" );

// Load configuration
$config = require __DIR__ . '/config.php';
$dependencies                       = $config['dependencies'];
$dependencies['services']['config'] = $config;

// Build container
return new ServiceManager($dependencies);
