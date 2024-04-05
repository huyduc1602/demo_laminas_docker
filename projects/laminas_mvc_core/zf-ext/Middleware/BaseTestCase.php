<?php
namespace Zf\Ext\Middleware;

use Laminas\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Laminas\Stdlib\ArrayUtils;
use \Laminas\Authentication\Storage\Session;
/**
 * @author Jilv006
 */
class BaseTestCase extends AbstractHttpControllerTestCase
{
    protected $traceError = true;
    
    /**
     * Request headers
     * @var array
     */
    public static $headers = [
        '_COOKIE' => [
            //'PHPSESSID' => '',
        ],
        '_SERVER' => [
            //'REDIRECT_URL'  => '/',
            //'REQUEST_URI'   => '/',
            'HTTPS'         => 'On',
            'REQUEST_SCHEME'=> 'https',
            'SERVER_ADDR'   => '127.0.0.1',
            'CLIENT_IP'     => '127.0.0.1',
            'REMOTE_ADDR'   => '127.0.0.1',
            'REQUEST_TIME'  => 0
        ],
        '_GET' => []
    ];
    /**
     * User information
     * @var array
     */
    public static $authenData = null;
    /**
     * Application configs
     */
    public static $configsPath = '';
    
    public function setUp() : void
    {
        $GLOBALS['_SERVER'] = array_merge(
            $GLOBALS['_SERVER'] ?? [], self::$headers['_SERVER']
        );
        $GLOBALS['_GET'] = self::$headers['_GET'];
        
        $configOverrides = [];
        $this->setApplicationConfig(ArrayUtils::merge(
            include self::$configsPath . '/application.config.test.php',
            $configOverrides
        ));
        
        parent::setUp();
    }
    public static $ssId = '';
    /**
     * Write user information to session
     * @param array $authenData {
     *  'admin_id'       => 1,
     *  'admin_code'     => 'biDaf7T3lljdV1438KU',
     *  'admin_fullname' => 'Supper admin',
     *  'admin_email'    => 'example@gmail.com',
     *  'admin_groupcode'=> 'SUPPORT',
     *  'admin_status'   => 1,
     *  'admin_last_access_time' => time(),
     *  'admin_avatar'   => 'C4Ba6jdkF1Pg3171.jpg',
     *  'admin_phone'    => '',
     *  'admin_bg_timeline' => ''
     * }
     */
    protected function makeAuthen(array $authenData = []){
        @session_id(self::$ssId = @session_create_id());
        
        $auService = $this->getApplication()->getServiceManager()
        ->get(\Laminas\Authentication\AuthenticationService::class);
        
        $auService->getStorage()->write((object)$authenData);
        
        session_commit();
        session_write_close(); 
    }

    /**
     * @param string $key
     */
    public function assertFlashMessageByKey(string $key){
        $fM = $this->getApplication()->getServiceManager()
            ->get('ViewHelperManager')->get('flashMessenger');
        try {
            $this->assertEquals(true, $fM->hasCurrentMessages($key) || $fM->hasMessages($key));
        } catch (\Throwable $e) {
            $this->fail('Request dont have flashMessage: '. $key);
        }
    }
    /**
     * Get message from session
     * @param string $key
     */
    public function getFlashMessage(string $key){
        $flashMsgManager = $this->getApplication()->getServiceManager()
        ->get('ViewHelperManager')
        ->get('flashMessenger');
        
        $key = ucfirst($key);
        $msg = $flashMsgManager->{"getCurrent{$key}Messages"}();
        if (empty($msg)) $msg = $flashMsgManager->{"get{$key}Messages"}();
        
        unset($flashMsgManager);
        return array_shift($msg);
    }
    /**
     * Make csrf token
     * @return string
     */
    public function createCsrfToken($opts = []){
        return $this->getApplication()->getServiceManager()
        ->get('ViewHelperManager')
        ->get('zfCsrfToken')
        ->generalCsrfToken(
            array_merge([time()], $opts)
        );
    }
    /**
     * Get respone body
     * @return mixed
     */
    public function getResponeAsString(){
        $json = $this->getResponse()->getContent();
        if( $json instanceof \Laminas\View\Model\JsonModel ){
            return $json->serialize();
        }
        return (string) $json;
    }
    const DENY_ELEMENT_ID = '#permission_deny_for_this_user';
    /**
     * Access valid
     */
    public function assertNotDenyRequest(string $msg = null){
        try {
            $this->assertQueryCount(self::DENY_ELEMENT_ID, 0);
        } catch (\Throwable $e) {
            $this->fail($msg ?? 'Access denies');
        }
    }
    /**
     * Permission denies for current user
     */
    public function assertDenyRequest(string $msg = null){
        try {
            $this->assertQueryCountMin(self::DENY_ELEMENT_ID, 1);
        } catch (\Throwable $e) {
            $this->fail($msg ?? 'Access not denies');
        }
    }
    /**
     * Is normal request
     */
    public function assertNotFoundDispatch(){
        $this->assertResponseStatusCode(404);
    }
    /**
     * Is normal request
     */
    public function assertNormalDispatch(bool $notDeny = true){
        $this->assertResponseStatusCode(200);
        if ($notDeny) $this->assertNotDenyRequest();
        else $this->assertDenyRequest();
    }
    /**
     * Is normal request
     */
    public function assertRedirectDispatch( string $toUrl ){
        $this->assertRedirect();
        $this->assertRedirectTo($toUrl);
    }
    /**
     * Is normal request
     */
    public function assertRedirectDispatchRegex( string $pattern ){
        $this->assertRedirect();
        $this->assertRedirectRegex($pattern);
    }
    /**
     * Assert and get respone as array
     * @return array
     */
     public function assertGetJsonRespone(){
        $this->assertJson($json = $this->getResponeAsString(), 'Respone invalid json string');
        $json = @json_decode($json, true) ?? null;
        $this->assertIsArray($json, 'Respone invalid json data');
        return $json;
    }
    /**
    * Request not found
    */
    public function assertRequestNotFound( ){
        $this->assertResponseStatusCode(404);
    }
    /**
     * Get message from layout
     * @param string $type
     * @param string $pattern
     * @return mixed
     */
    public function getMsgFromLayout(string $type, string $pattern = null){
        $json = @json_decode(
            $this->getErrorMsgFromBody(2, $pattern ?? '/(msgs\s\=\s{0,1}(\{.*\})\;)/m')
            ?? '{}',
            true
        ) [$type] ?? [];
            
        return array_pop($json);
    }
    /**
     * Get message from body
     * @param int $index
     * @param string $pattern
     * @return NULL|string
     */
    public function getErrorMsgFromBody(int $index = 5, string $pattern = null){
        $matchs = [];
        preg_match(
            $pattern ?? '/(msgs(\s){0,1}\=(\s){0,1}\{\"(error|warning|info|success)\"\:[\[\{]\"(.*)\"[\]\]))/mi',
            (string) $this->getResponeAsString(), 
            $matchs
        );
        
        return $matchs[$index] ?? null;
    }
    /**
     * Upload file request
     * @param string $url
     * @param string $method
     * @param array $params
     * @param bool $isXmlHttpRequest
     * @param array $files
     */
    public function dispatchWithFile(
        $url, $method = null, $params = [], $isXmlHttpRequest = false,
        $files = []
    ){
        if ( !empty($files) ){
            $GLOBALS['_FILES'] = $files;
        }
        $this->dispatch($url, $method, $params, $isXmlHttpRequest);
    }
    /**
     * Redis cache manager
     * @var \Laminas\Cache\Storage\StorageInterface
     */
    protected static $cacheCore = null;
    /**
     * Get redis cache core
     * @param array $opts
     * @param string $lifetime
     * @return \Laminas\Cache\Storage\StorageInterface
     */
    protected static function getCacheCore(array $opts, $lifetime = false){
        if ( empty(self::$cacheCore) ){
            if ( class_exists('Laminas\Cache\StorageFactory') ){
                self::$cacheCore = \Laminas\Cache\StorageFactory::factory([
                    'adapter' => [
                        'name' => 'Redis',
                        'options' => array_merge([
                            'namespace' => ($opts['namespace'] ?? 'redis_cache'),
                            'ttl'       => $lifetime,
                        ], REDIS_CONFIG)
                    ],
                    'plugins' => [
                        // Don't throw exceptions on cache errors
                        'exception_handler' => [
                            'throw_exceptions' => false
                        ],
                        'Serializer'
                    ]
                ]);
            }else {
                self::$cacheCore = new \Zf\Ext\LaminasRedisAdapter (
                    (new \Laminas\Cache\Storage\Adapter\Redis (
                        array_merge([
                            'namespace' => DOMAIN_NAME,
                            'ttl'       => $lifetime,
                        ], $opts, REDIS_CONFIG)
                    ))
                    
                    ->addPlugin(
                        (new \Laminas\Cache\Storage\Plugin\ExceptionHandler())
                        ->setOptions(new \Laminas\Cache\Storage\Plugin\PluginOptions([
                            'throw_exceptions' => true
                        ]))
                    )
                    ->addPlugin(
                        new \Laminas\Cache\Storage\Plugin\Serializer()
                    )
                );
            }
        }
        return self::$cacheCore;
    }
    /**
     * Name space of session
     * @var string
     */
    public static $sessNamespace = null;
    
    /**
     * @afterClass
     */
    public static function tearDownDeleteSessionFiles(): void
    {
        if ( !empty(self::$ssId) && defined('REDIS_CONFIG') ){
            
            self::getCacheCore(
                ['namespace' => self::$sessNamespace],
                ini_get('session.cookie_lifetime') ?? false
            )
            ->removeItem(self::$ssId)
            ;
            
            self::$ssId = '';
        }
    }
}