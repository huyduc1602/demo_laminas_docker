<?php
use Laminas\Mvc\Application;
use Laminas\Stdlib\ArrayUtils;

try{
    error_reporting(E_ALL & ~ E_NOTICE & ~ E_WARNING);
    // -- Turn off all error reporting
    //error_reporting(0);
    
    ini_set('default_charset', 'utf-8');
    
    // Time zone
    date_default_timezone_set ( "Asia/Ho_Chi_Minh" );
    //date_default_timezone_set ("Asia/Tokyo");
    
    /**
     * This makes our life easier when dealing with paths. Everything is relative
     * to the application root now.
     */
    $dirName = dirname ( __FILE__ );
    chdir($dirName);
    require $dirName . '/error_handle.php';
    // Handle file not found
    
    $uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if ( preg_match('/.*(\.(ico|gif|jpe?g|png|bmp|jpg|pdf))$/', $uriPath) ){
        if ( !realpath($dirName . $uriPath) ){
            header('HTTP/1.0 404 Not Found', true, 404);
            exit();
        }
    }
    
    // Decline static file requests back to the PHP built-in webserver
    if (php_sapi_name() === 'cli-server') {
        
        $path = realpath($dirName . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        if (__FILE__ !== $path && is_file($path)) {
            return false;
        }
        unset($path);
    }
    
    /* if ( '/coming-soon' != $_SERVER['REQUEST_URI']){
        header("HTTP/1.1 301 Moved Permanently");
        header("Location: https://learningift.local/coming-soon");
        exit();
    } */
    defined ( 'LIBRARY_PATH' ) || define ( 'LIBRARY_PATH','/var/www/lib_php/laminas_mvc_core/vendor' );
    
    $baseAppDir = '/var/www/projects/demo';
    defined ( 'APPLICATION_PATH' ) || define ( 'APPLICATION_PATH', $baseAppDir . '/app/modules' );
    defined ( 'CONFIG_PATH' ) || define ( 'CONFIG_PATH', $baseAppDir . '/configs' );
    defined ( 'DATA_PATH' ) || define ( 'DATA_PATH', $baseAppDir . '/data' );
    
    defined ( 'PUBLIC_PATH' ) || define ( 'PUBLIC_PATH', $dirName );
    defined ( 'DOMAIN_NAME' ) || define ( 'DOMAIN_NAME', $_SERVER['SERVER_NAME'] ?? '' );
    
    defined ( 'COOKIE_DOMAIN' ) || define ( 'COOKIE_DOMAIN', $_SERVER['SERVER_NAME'] ?? '' );
    
    // 
    $configSites = require_once CONFIG_PATH . '/sites-config.php';
    $uris = explode('/', 
        ltrim(rtrim($_SERVER['REQUEST_URI'] ?? '', '/'), '/'), 
    2);
    
    $baseUrl = '/'. ltrim(array_shift($uris), '/');
    
    foreach ( $configSites as $config ) {
        
        list ( $domains, $users ) = $config;
        
        if ( in_array(DOMAIN_NAME, $domains) ) {
            if( !isset($users[$baseUrl]) ) $baseUrl = '/';
            
            $tructure = $users[$baseUrl] ?? [];
            
            // Define site
            defined ( 'APPLICATION_SITE' ) || define ( 'APPLICATION_SITE', $tructure ['site'] );
            defined ( 'SESSION_PATH' ) || define ( 'SESSION_PATH', DATA_PATH . "/session/{$tructure ['site']}" );
            defined ( 'SESSION_PREFIX' ) || define ( 'SESSION_PREFIX', 'PHP_SESS_'. APPLICATION_SITE . ':' );
            defined ( 'SESSION_LIFETIME' ) || define ( 'SESSION_LIFETIME', 604800 );
            
            defined ( 'CSRF_TOKEN_DIR' ) || define ( 'CSRF_TOKEN_DIR', $tructure ['csrf_token_dir'] ?? $tructure ['site']);
            //define ( 'APP_ENV_VERSON', 'release' );
            defined ( 'APP_ENV_VERSON' ) || define ( 'APP_ENV_VERSON', 'vtest' );
            defined ( 'APPLICATION_VERSION' ) || define ( 'APPLICATION_VERSION', 'vtest' );
            
            // Define base url
            defined ( 'BASE_URL' ) || define ( 'BASE_URL', $baseUrl );
            
            // Define
            defined ( 'FOLDER_UPLOAD_BY_SITE' ) || define ( 'FOLDER_UPLOAD_BY_SITE', $tructure ['upload-folder'] );
            
            // Dinh nghia ngon ngu
            defined ( 'APPLICATION_LANGUAGE' ) || define('APPLICATION_LANGUAGE', $tructure['language']??'ja');
            
            // -- Dinh nghia locale
            defined ( 'APPLICATION_LOCALE' ) || define('APPLICATION_LOCALE', $tructure['locale']??'ja_JP');
            
            // -- Dinh nghia locale
            $skinName = $tructure['skin-name'] ?? 'assets';
            defined ( 'APPLICATION_SKIN_NAME' ) || define('APPLICATION_SKIN_NAME', $skinName);
            
            defined('APPLICATION_DATE') || define('APPLICATION_DATE', $tructure['date']['date'] ?? 'Y/n/j');
            defined('APPLICATION_DATE_TIME') || define('APPLICATION_DATE_TIME', $tructure['date']['date-time'] ?? 'Y/n/j G:i');
            defined('APPLICATION_DATE_MONTH') || define('APPLICATION_DATE_MONTH', $tructure['date']['month'] ?? 'n/j');
            defined('APPLICATION_MONTH') || define('APPLICATION_MONTH', $tructure['date']['monthShort'] ?? 'Y/n');
            defined('APPLICATION_HOUR') || define('APPLICATION_HOUR', $tructure['date']['hour'] ?? 'G:i');
            defined('APPLICATION_DATE_STR') || define('APPLICATION_DATE_STR', $tructure['date']['date-str'] ?? 'Y年n月j日');
            defined('APP_MYSQL_DATE') || define('APP_MYSQL_DATE', $tructure['date']['mysqlDate'] ?? '%d/%m/%Y');
            defined('APP_MYSQL_DATE_TIME') || define('APP_MYSQL_DATE_TIME', $tructure['date']['mysqlDateTime'] ?? '%Y/%m/%d %H:%i');
            defined('APP_JS_DATE') || define('APP_JS_DATE', $tructure['date']['jsDate'] ?? 'YYYY/M/D');
            defined('APP_JS_DATE_TIME') || define('APP_JS_DATE_TIME', $tructure['date']['jsDateTime'] ?? 'YYYY/M/D H:mm');
            defined('APP_JS_DATE_IN_WEEK') || define('APP_JS_DATE_IN_WEEK', $tructure['date']['jsDateInWeek'] ?? 'M月D日（dddd）');
            defined('APP_JS_TIME_DATE') || define('APP_JS_TIME_DATE', $tructure['date']['jsTimeDate'] ?? 'MM/D H:mm');
            defined('APP_JS_MONTH') || define('APP_JS_MONTH', $tructure['date']['jsMonth'] ?? 'YYYY/M');
            defined('APP_CHAT_TIME') || define('APP_CHAT_TIME', $tructure['date']['chatTime'] ?? 'n/j G:i');
            defined('APP_JS_TIME') || define('APP_JS_TIME', $tructure['date']['jsTime']?? 'H:mm');
            // Break
            break;
        }
    }
    
    // Case: Site not found
    if ( empty(defined('APPLICATION_SITE')) ) {
        exit( 'Uri is invalid!' );
    }
    // Composer autoloading
    include LIBRARY_PATH . '/autoload.php';
    include APPLICATION_PATH . '/' . APPLICATION_SITE . '/vendor/autoload.php';
    
    if (! class_exists(Application::class)) {
        throw new RuntimeException(
            "Unable to load application.\n"
        );
    }
    
    // Retrieve configuration
    $appConfig = ArrayUtils::merge(
        require CONFIG_PATH . '/application.config.php', 
        require CONFIG_PATH . '/sites/'. APPLICATION_SITE .'.php'
    );
    
    if (file_exists(CONFIG_PATH . '/development.config.php')) {
        $appConfig = ArrayUtils::merge($appConfig, require CONFIG_PATH . '/development.config.php');
    }
    
    // System constant
    require CONFIG_PATH . '/application.constant.php';
        
    try{
        Application::init($appConfig)->run();
    }catch (\Throwable $e){
        echo '<pre>';
        var_dump($e->getMessage(), $e->getTraceAsString());
        echo '</pre>';
        exit();
    }
}catch (\Throwable $e){
    exit($e->getMessage());
}