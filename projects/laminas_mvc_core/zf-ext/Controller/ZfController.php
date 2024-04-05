<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zf\Ext\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Doctrine\Common\Cache;
/**
 * @todo       allow specifying status code as a default, or as an option to methods
 */
class ZfController extends AbstractActionController
{
    /**
     * Default message on error occurred
     */
    const DEFAULT_ERROR_MSG = 'Some error occurred';

    /**
     * Function Generate CsrfToken
     * @param array $unique
     * @param null $userFolder
     * @param null $site
     * @param int $lifetime
     * @return mixed
     * @author Cuong_dev - 3/30/2022
     */
    public function generateCsrfToken( $unique = [], $userFolder = null, $site = null, int $lifetime = 86400) {
        return $this->zfCsrfToken()->generalCsrfToken( $unique, $userFolder, $site, $lifetime);
    }
    /**
     * @param null $token
     * @param null $userFolder
     * @param int $lifetime
     * @return mixed
     * @author Cuong_dev - 3/30/2022
     */
    public function isValidCsrfToken( $token = null, $userFolder = null, $lifetime = 86400) {
        return $this->zfCsrfToken()->isValidCsrfToken($token, $userFolder, $lifetime);
    }
    /**
     * @param null $token
     * @param null $userFolder
     * @return mixed
     * @author Cuong_dev - 3/30/2022
     */
    public function clearCsrfToken( $token = null, $userFolder = null){
        return $this->zfCsrfToken()->clearCsrfToken($token, $userFolder);
    }

    /**
     * @param $routeName
     * @param array $opts
     * @return mixed
     * @author Cuong_dev - 3/30/2022
     */
    public function redirectToRoute(string $routeName = '', $opts = []) {
        if (empty($routeName))
            $routeName = $this->getCurrentRouteName();

        return $this->zfRedirect()->toRoute($routeName, $opts);
    }
    /**
     * Get data from header
     * @param string $key
     * @return mixed
     */
    public function getParamHeader( $key = '' ){
        $data = $this->params()->fromHeader($key);
        if ( $data ) return $data->getFieldValue();
        return null;
    }
    /**
     * @param null $param
     * @param null $default
     * @return mixed
     * @author Cuong_dev - 4/23/2021
     */
    public function paramsPost($param = null, $default = null) {
        return $this->params()->fromPost($param, $default);
    }
    /**
     * @param null $param
     * @param null $default
     * @return mixed
     * @author Cuong_dev - 4/23/2021
     */
    public function paramsQuery($param = null, $default = null) {
        return $this->params()->fromQuery($param, $default);
    }
    /**
     * @param null $param
     * @param null $default
     * @return mixed
     * @author Cuong_dev - 4/23/2021
     */
    public function paramsRoute($param = null, $default = null) {
        return $this->params()->fromRoute($param, $default);
    }
    /**
     * @param null $param
     * @param null $default
     * @return array|\ArrayAccess|null
     * @author Cuong_dev - 4/23/2021
     */
    public function paramsFiles($param = null, $default = null) {
        return $this->params()->fromFiles($param, $default);
    }
    /**
     * @return mixed
     * @author Cuong_dev - 4/23/2021
     */
    public function isPostRequest() {
        return $this->getRequest()->isPost();
    }
    /**
     * @param string $msg
     * @author Cuong_dev - 4/23/2021
     */
    public function addErrorMessage(string $msg = '') {
        $this->flashMessenger()->addErrorMessage(
            $this->mvcTranslate($msg)
        );
    }
    /**
     * @param string $msg
     * @author Cuong_dev - 4/23/2021
     */
    public function addSuccessMessage(string $msg = '') {
        $this->flashMessenger()->addSuccessMessage(
            $this->mvcTranslate($msg)
        );
    }
    /**
     * Get host by id
     * @param string $ip
     * @return string
     */
    protected function getHostByIP( $ip = '' ){
        $ip = gethostbyaddr($ip) ?? $ip;
        // Get provider
        $host = preg_replace('/(\d+\.)/', '', $ip);
        if ( false == empty($host) ) return $host;
        return $ip;
    }
    
    /**
     * Get device info
     * @return string || null
     */
    public function getDevice()
    {
        $default = [
            'browser'       => 'UNKNOW',
            // --- App Info ------
            'agent'         => $this->getParamHeader('user-agent'),
            'device'        => 'UNKNOW',
            'version'       => 'UNKNOW',
            'type'          => 'UNKNOW',
            'os'            => 'UNKNOW',
            'os_version'    => 'UNKNOW',
            'ip_address'    => $ip = $this->getZfHelper()->getClientIp(),
            'hostname'      => $this->getHostByIP($ip),
            'is_mobile'     => null,
        ];
        try{
            $piwikParser = new \DeviceDetector\DeviceDetector(
                $default['agent'] ?? ''
            );
            $cache = null;
            if ( class_exists('Doctrine\Common\Cache\PhpFileCache') ){
                $cache = new \DeviceDetector\Cache\DoctrineBridge(
                    new \Doctrine\Common\Cache\PhpFileCache( DATA_PATH . '/cache/piwik')
                );
            }elseif( !empty($cacheCore = $this->getZfCacheFile()) ){
                $cache = new \Zf\Ext\ZFCacheFileForDeviceDetector($cacheCore);
            }
            
            if ($cache) $piwikParser->setCache($cache);
            
            $piwikParser->parse();
            
            if( $piwikParser->isBot() === true ) {
                $botInfo = $piwikParser->getBot();
                $default['browser'] = $botInfo['name'] ?? '';
    
                $default['os'] = $botInfo['category'] ?? '';
                $default['device'] = 'BOT';
            }
            else{
                $client = $piwikParser->getClient();
                $osParse = $piwikParser->getOs();
                $default['browser'] = $client['name'] ?? '';
                $default['version'] = $client['version'] ?? '';
                
                $osName = $osParse['name'] ?? '';
                if ( $osName ){
                    $default['os'] = $osName;
                    $default['os_version'] = $osParse['version'] ?? '';
                }
                $device = $piwikParser->getDeviceName();
                if ( $device ){
                    $default['device'] = $device;
                }
                if ( !empty($client['type']) ){
                    $default['type'] = strtoupper($client['type']);
                }
                
                $default['is_mobile'] = $piwikParser->isMobileApp();
            }
        }catch (\Throwable $e){
            $this->saveErrorLog($e);
        }
    
        /* $deviceId = $this->_request->getHeader('Device-Id');
         if( $deviceId && '' != $deviceId[0] ) $default['id'] = $deviceId[0];
    
         $appVersion = $this->_request->getHeader('App-Version');
         if( $appVersion && '' != $appVersion[0] ) $default['app_version'] = $appVersion[0];
         */
        return $default;
    }

    /**
     * Get the repository for an entity class.
     * @param $entityName
     * @return \Doctrine\ORM\EntityRepository|\Doctrine\Persistence\ObjectRepository
     * @author Cuong_dev - 4/19/2021
     */
    public function getEntityRepo($entityName, $connectionName = 'orm_default')
    {
        return $this->getEntityManager($connectionName)->getRepository($entityName);
    }

    /**
     * Get the connection of EntityManager
     * @return \Doctrine\DBAL\Connection
     * @author Cuong_dev - 4/19/2021
     */
    public function getEntityConnection($connectionName = 'orm_default') {
        return $this->getEntityManager($connectionName)->getConnection();
    }

    /**
     * EntityManager rollback
     * @author Cuong_dev - 4/19/2021
     */
    public function rollbackTransaction($connectionName = 'orm_default') {
        $this->getEntityManager($connectionName)->rollback();
    }

    /**
     * EntityManager commit
     * @author Cuong_dev - 4/19/2021
     */
    public function commitTransaction($connectionName = 'orm_default') {
        $this->getEntityManager($connectionName)->commit();
    }

    /**
     * EntityManager beginTransaction
     * @author Cuong_dev - 4/19/2021
     */
    public function startTransaction($connectionName = 'orm_default') {
        $this->getEntityManager($connectionName)->beginTransaction();
    }

    /**
     * EntityManager startTransactional
     * @author Cuong_dev - 01/21/2022
     */
    public function startTransactional($func, $connectionName = 'orm_default') {
        try {
            
            $this->getEntityManager($connectionName)->beginTransaction();
            call_user_func($func);
            $this->getEntityManager($connectionName)->commit();
            
            return true;
        } catch (\Throwable $e) {
            $this->getEntityManager($connectionName)->rollback();
            
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }
    /**
     * @param \Doctrine\ORM\Query $query
     * @param int $limit
     * @param int $page
     * @return \Laminas\Paginator\Paginator
     * @author Cuong_dev - 4/19/2021
     */
    public function getZfPaginator($query, int $limit = 20, int $page = 1) {
        return $this->getPaginator($query)
            ->setItemCountPerPage($limit)
            ->setCurrentPageNumber($page);
    }

    /**
     * EntityManger createQueryBuilder
     * @return \Doctrine\ORM\QueryBuilder
     * @author Cuong_dev - 4/19/2021
     */
    public function createZfQueryBuilder($connectionName = 'orm_default') {
        return $this->getEntityManager($connectionName)->createQueryBuilder();
    }
    /**
     * EntityManager
     * @return \Doctrine\ORM\EntityManager
     */
    public function zfDoctrineManager($connectionName = 'orm_default') {
        return $this->getEntityManager($connectionName);
    }
    /**
     * Truncate data before insert to database
     * @param array $items
     * @return array
     */
    protected function makeParamsForInsert(array $items ){
        foreach ( $items as $idx => $item ){
            if ( is_array($item) )
                $items[$idx] = $this->makeParamsForInsert($item);
            elseif( is_string($item) ){
                if ( in_array($idx, ['password', 'pw']) ){
                    $item = str_repeat('*', strlen($item??''));
                }

                $items[$idx] = htmlspecialchars(trim(mb_substr($item, 0, 255)));
            }

        }
        return $items;
    }
    /**
     * Auto send email to admin
     */
    protected function sendMailWarningError($content = ''){
        if ( defined('ERRO_AUTO_SEND_MAIL') && !empty(ERRO_AUTO_SEND_MAIL) ){
            try {
                return \Zf\Ext\Utilities\ZFTransportSmtp::sendMailNew([
                    'to'        => ERRO_AUTO_SEND_MAIL,
                    'toName'    => 'System Administator',
                    
                    'from'      => SIGN_UP_EMAIL,
                    'fromName'  => DOMAIN_NAME ?? 'System',
                    
                    'replyTo'   => NO_REPLY_EMAIL,
                    'title'     => 'Your service got an error. Please check it',
                    'msg'       => $content,
                    'encoding'  => \Laminas\Mime\Mime::ENCODING_QUOTEDPRINTABLE
                ], $this->getEntityManager()->getConnection());
            }catch (\Throwable $e){
            }
        }
    }
    
    /**
     * Save error log
     * @param \Throwable $e
     */
    public function saveErrorLog( $e ){
        if (function_exists('\Sentry\captureException')) 
            @\Sentry\captureException($e);
        
        $user = $this->getAuthen();
        $params = [
            'post' => $this->params()->fromPost(),
            'get' => $this->params()->fromQuery(),
        ];
        $pathApp = realpath( APPLICATION_PATH . '/../../');
        $pathLib = realpath( LIBRARY_PATH . '/../');
        $pathPub = realpath( PUBLIC_PATH . '/../');
        foreach ( $params as $key => $items ){
            $params[$key] = $this->makeParamsForInsert($items);
        }
        
        $url = mb_substr((string)$this->getRequest()->getUri()->getPath(), 0, 200);
        $msg = $e->getMessage();
        
        $this->sendMailWarningError("Uri: {$url}<br>Message: {$msg}");
        
        $this->getEntityManager()
        ->getConnection()
        ->insert('tbl_error', [
            'error_user_id' => $user ? ($user->{$user->authen_key} ?? null) : null,
            'error_uri'     => $url,
            'error_params'  => json_encode($params),
            'error_method'  => ($this->getRequest()->isPost() ? 'POST' : 'GET'),
            'error_msg'     => 'Message: '. str_replace([$pathApp, $pathPub, $pathLib], '', substr($msg, 0, 2000))
            . ".\nOn line: "  . $e->getLine()
            . ".\nOf file: " . str_replace([$pathApp, $pathPub, $pathLib], '', $e->getFile()),
            'error_trace'   => str_replace([$pathApp, $pathPub, $pathLib], '', substr($e->getTraceAsString(),0, 6000)),
            'error_code'    => $e->getCode(),
            'error_time'    => time()
        ]);
        /*
        $putCmd= realpath(DATA_PATH . '/shell_scripts/push_notify_error.sh');
        if ( !empty($putCmd) ){
            $now = date('Y/m/d H:i:s');
            $env = APP_ENV_VERSON;
            $domain = strtoupper(DOMAIN_NAME);
            $url = $url ?? ($_SERVER['REQUEST_URI'] ?? '');
            $file = str_replace(realpath(APPLICATION_PATH . '/../../'), '', $e->getFile());
            @shell_exec(vsprintf('sh %s %s', [
                $putCmd,
                escapeshellarg(str_replace("'", '', "[{$now} - {$env}] {$domain} Problem: {$url}\n{$e->getMessage()}\nAt line: {$e->getLine()} of file: {$file}"))
            ]));
        }*/
    }
    /**
     * FlashMessenger
     * @var \Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger
     * @return \Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger
     */
    public function zfFlashMsg() {
        return $this->flashMessenger();
    }
    /**
     * Common helper
     * @var \Zf\Ext\Utilities\ZFHelper
     */
    public static $_common = null;
    /**
     * ZFHelper
     * @var \Zf\Ext\Utilities\ZFHelper
     * @return \Zf\Ext\Utilities\ZFHelper
     */
    public function getZfHelper() {
        if ( !self::$_common ) 
            self::$_common = new \Zf\Ext\Utilities\ZFHelper();
        return self::$_common;
    }
    
    public static $_escap = null;
    
    public function getEscap() {
        if( !self::$_escap ){
            self::$_escap = new \Laminas\Escaper\Escaper('utf-8');
        }
        return self::$_escap;
    }
    
    /**
     * @param \Doctrine\ORM\Query $query 
     * @param bool $simpleCount
     * @return \Zf\Ext\Model\ZFDtPaginator
     */
    public function getDoctrinePaginator($query, $simpleCount = false){
        return $this->getPaginator($query, $simpleCount);
    }
    
    /**
     * @param Get match route name
     * @return string
     */
    public function getCurrentRouteName(){
        return $this->getEvent()->getRouteMatch()->getMatchedRouteName();
    }
    /**
     * Get current ID of login user
     * @return integer | null
     */
    public function getLoginId(){
        $key = $this->getAuthen()->authen_key ?? '';
        return empty($key) ? null : ($this->getAuthen()->{$key} ?? null);
    }
    
    /**
     * Check is valid datetime string
     * @param string $str
     */
    public function isValidFullDatetimeStr($str = ''){
        if (empty($str)) return false;
        
        // Valid pattern: "2021.04.20 20:00:00" or "2021/04/20 20:00:00"
        if (
            // JP
            preg_match(
                '/^(\d{4}[\/\.]\d{1,2}[\/\.]\d{1,2})\s(\d{1,2}\:\d{1,2}\:\d{1,2})$/',
                $str
            )
            // VI
            || preg_match(
                '/^(\d{1,2}[\/\.]\d{1,2}[\/\.]\d{4})\s(\d{1,2}\:\d{1,2}\:\d{1,2})$/',
                $str
            )
        ) return true;
        
        return false;
    }
}
