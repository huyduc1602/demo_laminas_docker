<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zf\Ext\Controller;

use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use Interop\Container\ContainerInterface;
use Laminas\Authentication\AuthenticationService;
/**
 * @todo       allow specifying status code as a default, or as an option to methods
 */
class ZfCsrfToken extends AbstractPlugin
{
    const SERVICE_ALIAS = 'zfCsrfToken';
    /**
     * CsrfToken
     * @var \Zf\Ext\Utilities\CsrfToken
     */
    protected static $csrfToken = null;
    protected static $userKey = '';
    
    public function __construct(ContainerInterface $container){
        if ( !self::$csrfToken ){
            self::$csrfToken = new \Zf\Ext\Utilities\CsrfToken();
        }
        if ( $container->has(AuthenticationService::class) ){
            $authen = $container->get(AuthenticationService::class)->getIdentity();
            $configs = $container->get('config');
            
            if (!empty($authen)){
                if (isset($configs['csrf_token']) 
                    && isset($configs['csrf_token'][APPLICATION_SITE]))
                    self::$userKey = $authen->{$configs['csrf_token'][APPLICATION_SITE]} ?? '';
                else self::$userKey = $authen->user_code ?? $authen->admin_code ?? '';
            }else self::$userKey = '';
            
            unset($configs, $authen);
        }
    }
    
    public function __invoke( $useOneTime = true ) {
        if( !is_bool($useOneTime) ){
            $useOneTime = true;
		}
		self::$csrfToken->_useOneTime = $useOneTime;
        return $this;
    }
    
    /**
     * Create CSRF token
     * @param array $unique
     * @param string $userFolder
     * @return string
     */
    public function generalCsrfToken( $unique = [], $userFolder = null, $site = null, int $lifetime = 86400){
        
        return self::$csrfToken->generalCsrfToken(
            $userFolder ?? self::$userKey, $unique, $site, $lifetime
        );
    }
    
    /**
     * Get token from header of request
     * @return string
     */
    protected function getToken( $token = null ){
        $token = $token ?? $this->getController()->params()->fromHeader('Csrf-Token', '');
        
        if ( $token && is_object($token)) $token = $token->getFieldValue();
        return $token;
    }
    /**
     * Check CSRF token
     * @param string $token
     * @param string $userFolder
     * @param integer $lifetime
     * @return bool, true if token is valid
     */
    public function isValidCsrfToken( $token = null, $userFolder = null, $lifetime = 86400, $site = null){

        $token = $this->getToken($token);
        return self::$csrfToken->isValidCsrfToken(
            $userFolder ?? self::$userKey, $token, $lifetime, $site
        );
    }
    
    /**
     * Clear CSRF token
     * @param string $token
     * @param string $userFolder
     * @return bool, true if success
     */
    public function clearCsrfToken( $token = null, $userFolder = null, $site = null){
        $token = $this->getToken($token);
        return self::$csrfToken->clearCsrfToken(
            $userFolder ?? self::$userKey, $token, $site
        );
    }
}
