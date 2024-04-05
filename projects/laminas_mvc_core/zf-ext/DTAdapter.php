<?php
namespace Zf\Ext;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

class DTAdapter
{
    /**
     * Key to get EntityManager
     * @var string
     */
    const ENTITY_MANAGER_KEY = 'ZF_EntityManager';
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    public static $_entityManager = null;
    
    /**
     * @var array
     */
    public $_config = [];
    
    public function __construct(array $config){
        $this->_config = $config;
    }
    
    /**
     * Inject the UrlHelper instance with a RouteResult, if present as a request attribute.
     *
     * Injects the helper, and then dispatches the next middleware.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        if ($next) {
            
            if ( !self::$_entityManager ) $this->initDT();
            
            return $next(
                $request->withAttribute(self::ENTITY_MANAGER_KEY, self::$_entityManager), 
                $response
            );
        }
        return $response;
    }
    
    protected function initDT() {
        //require_once LIBRARY_PATH . "/beberlei/doctrineExtensions/vendor/autoload.php";
        /**
         * khai bao cac duong dan den cac thu muc chua Entities, Repositories, Proxies...
        */
        $driverConfig = $this->_config['driver'];
        // Entities.
        $entitiesLoader = new \Doctrine\Common\ClassLoader ( 'Entities', $driverConfig ['DTAnnotationDriver']['paths'][0] );
        $entitiesLoader->register ();
        // Repositories.
        $repositoriesLoader = new \Doctrine\Common\ClassLoader ( 'Repositories', $driverConfig ['DTAnnotationDriver']['paths'][0] );
        $repositoriesLoader->register ();
        // Proxies.
        $proxiesLoader = new \Doctrine\Common\ClassLoader ( 'Proxies', $driverConfig ['DTAnnotationDriver']['paths'][0] );
        $proxiesLoader->register ();
    
        // Proxies.
        $utilitiesLoader = new \Doctrine\Common\ClassLoader ( 'Utilities', $driverConfig ['DTAnnotationDriver']['paths'][0] );
        $utilitiesLoader->register ();
    
        // Doctrine extensions.
        $doctrineExtensionsLoader = new \Doctrine\Common\ClassLoader ( 'DoctrineExtensions', $driverConfig ['DTAnnotationDriver']['libraryPath'] );
        $doctrineExtensionsLoader->register ();
    
        /**
         * Kiem tra va thay the cac loi de tranh truong hop sai cac duong dan
        */
        // Get other directories
        $entitiesDirectory = str_replace ( '//', '/', "{$driverConfig ['DTAnnotationDriver']['paths'][0]}/Entities" );
        $proxiesDirectory = str_replace ( "//", "/", "{$driverConfig ['DTAnnotationDriver']['paths'][0]}/Proxies" );
        $cache = new \Doctrine\Common\Cache\ArrayCache ();
        $isDevMode = true;
    
        /**
         * Init doctrine resource
         */
        $paths = [$entitiesDirectory];
        $config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode, $proxiesDirectory, $cache);
    
        $config->setCustomDatetimeFunctions([
            //'DATEADD'  => 'DoctrineExtensions\Query\Mysql\DateAdd',
            //'DATEDIFF' => 'DoctrineExtensions\Query\Mysql\DateDiff',
            //'DATESUB'  => 'DoctrineExtensions\Query\Mysql\DateSub',
            'FROM_UNIXTIME' => 'DoctrineExtensions\Query\Mysql\FromUnixtime',
            'UNIX_TIMESTAMP' => 'DoctrineExtensions\Query\Mysql\UnixTimestamp'
        ]);
    
        $config->setCustomNumericFunctions([
            //'ACOS'      => 'DoctrineExtensions\Query\Mysql\Acos',
            //'ASIN'      => 'DoctrineExtensions\Query\Mysql\Asin',
            //'ATAN'      => 'DoctrineExtensions\Query\Mysql\Atan',
            //'ATAN2'     => 'DoctrineExtensions\Query\Mysql\Atan2',
            //'BIT_COUNT' => 'DoctrineExtensions\Query\Mysql\BitCount',
            //'BIT_XOR'   => 'DoctrineExtensions\Query\Mysql\BitXor',
            //'COS'       => 'DoctrineExtensions\Query\Mysql\Cos',
            //'COT'       => 'DoctrineExtensions\Query\Mysql\Cot',
            //'DEGREES'   => 'DoctrineExtensions\Query\Mysql\Degrees',
            //'RADIANS'   => 'DoctrineExtensions\Query\Mysql\Radians',
            //'SIN'       => 'DoctrineExtensions\Query\Mysql\Sin',
            //'TAN'       => 'DoctrineExtensions\Query\Mysql\Tan',
            //'TAN'       => 'DoctrineExtensions\Query\Mysql\Tan',
            'RAND'       => 'DoctrineExtensions\Query\Mysql\Rand',
            'ROUND'       => 'DoctrineExtensions\Query\Mysql\Round'
        ]);
    
        $config->setCustomStringFunctions([
            //'ASCII'             => 'DoctrineExtensions\Query\Mysql\Ascii',
            //'CHAR_LENGTH'       => 'DoctrineExtensions\Query\Mysql\CharLength',
            //'CONCAT_WS'         => 'DoctrineExtensions\Query\Mysql\ConcatWs',
            //'FIELD'             => 'DoctrineExtensions\Query\Mysql\Field',
            //'FIND_IN_SET'       => 'DoctrineExtensions\Query\Mysql\FindInSet',
            //'LEAST'             => 'DoctrineExtensions\Query\Mysql\Least',
            //'LPAD'              => 'DoctrineExtensions\Query\Mysql\Lpad',
            'REPLACE'           => 'DoctrineExtensions\Query\Mysql\Replace',
            //'RPAD'              => 'DoctrineExtensions\Query\Mysql\Rpad',
            //'SOUNDEX'           => 'DoctrineExtensions\Query\Mysql\Soundex',
            'STR_TO_DATE'       => 'DoctrineExtensions\Query\Mysql\StrToDate',
            'SUBSTRING_INDEX'   => 'DoctrineExtensions\Query\Mysql\SubstringIndex'
        ]);
        
        return (self::$_entityManager = EntityManager::create($this->_config['connection'], $config));
    }
}
?>