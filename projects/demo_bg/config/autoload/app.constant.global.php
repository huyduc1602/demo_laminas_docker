<?php
/**
 * Define share constant
 */
$basePath = '/var/www/projects';
// -- Dir configs
defined ('KAFKA_TOPIC') || define('KAFKA_TOPIC', 'vtest_kafka');
defined ('KAFKA_TOPIC_SYNC') || define('KAFKA_TOPIC_SYNC', 'vtest_kafka');

defined ( 'APPLICATION_PATH' ) || define ( 'APPLICATION_PATH', "{$basePath}/ms_dev/app" );
defined ( 'LIBRARY_PATH' ) || define ( 'LIBRARY_PATH', '/var/www/projects/demo_bg/vendor' );

defined ( 'DATA_PATH' ) || define ( 'DATA_PATH', "{$basePath}/demo_bg/data" );
defined ( 'PUBLIC_PATH' ) || define ( 'PUBLIC_PATH', "{$basePath}/demo_bg/public_html" );
defined ( 'MAIN_CONFIG_PATH' ) || define ( 'MAIN_CONFIG_PATH',  "{$basePath}/demo_bg/config" );

defined ( 'MAIN_PUBLIC_PATH' ) || define ( 'MAIN_PUBLIC_PATH', PUBLIC_PATH );
defined ('ROOT_PUBLIC_PATH') || define('ROOT_PUBLIC_PATH', MAIN_PUBLIC_PATH);

defined ( 'CONFIG_PATH' ) || define ( 'CONFIG_PATH', "{$basePath}/ms_dev/config" );

// -- App setting
defined ( 'DOMAIN_NAME' ) || define ( 'DOMAIN_NAME', 'api.demo.local' );
defined ( 'MAIN_DOMAIN_NAME' ) || define ( 'MAIN_DOMAIN_NAME', DOMAIN_NAME);

defined ( 'PHP_UNIT_PATH' ) || define ('PHP_UNIT_PATH', '');

//define ( 'APP_ENV_VERSON', 'release' );
defined ( 'APP_ENV_VERSION' ) || define ( 'APP_ENV_VERSION', 'vtest' );
defined ( 'APPLICATION_VERSION' ) || define ( 'APPLICATION_VERSION', 'vtest' );

defined('APPLICATION_DATE') || define('APPLICATION_DATE', 'Y/m/d');
defined('APPLICATION_DATE_TIME') || define('APPLICATION_DATE_TIME', 'Y/m/d H:i');
defined('APPLICATION_DATE_MONTH') || define('APPLICATION_DATE_MONTH', 'Y/m');
defined('APPLICATION_HOUR') || define('APPLICATION_HOUR', 'H:i');

defined ('NO_REPLY_EMAIL') || define('NO_REPLY_EMAIL', 'no-reply@demo.local');
defined ('SIGN_UP_EMAIL') || define('SIGN_UP_EMAIL', 'sign-up@demo.local');

// Redis cache configs
defined ('REDIS_CONFIG') || define(
    'REDIS_CONFIG',
    (require __DIR__ .'/redis.global.php') ?? []
);

defined ('CUSTOMER_DOMAIN') || define('CUSTOMER_DOMAIN', 'customer.demo.local');
defined ('MANAGER_DOMAIN') || define('MANAGER_DOMAIN', 'customer.demo.local');

defined ( 'APP_REDIS_DB' ) || define ('APP_REDIS_DB', REDIS_CONFIG['database'] ?? 0);

defined ('NUM_USER_NOTIFY') || define('NUM_USER_NOTIFY', 101);

// Embed file app.constant from web source
// include MAIN_CONFIG_PATH . '/custom/application.constant.php';

return [];
?>