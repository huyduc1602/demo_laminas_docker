<?php
/**
* @category   ZF
* @package    ZF_View_Helper
* @subpackage BootstrapToolbar
* @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
* @version    $Id: BootstrapToolbar.php 2014-20-01
* @license    http://framework.zend.com/license/new-bsd     New BSD License
*/

/**
 * Zend_View_Helper_Abstract.php
 */
namespace Zf\Ext\View\Helper;
use Laminas\View\Helper\AbstractHelper;
use Interop\Container\ContainerInterface;
use Laminas\Authentication\AuthenticationService;
/**
 * Generates a "button" element into the toolbar
 *
 * @uses Zend_View_Helper_Abstract
 * @package ZF_View_Helper
 * @subpackage BootstrapToolbar
 * @copyright Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
class CsrfToken extends AbstractHelper {
const SERVICE_ALIAS = 'zfCsrfToken';
    /**
     * CsrfToken
     * @var Zf\Ext\Utilities\CsrfToken
     */
    protected static $csrfToken = null;
    /**
     * Base folder of user
     * @var string
     */
    protected $_userFolder = '';
    
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
                    $this->_userFolder = $authen->{$configs['csrf_token'][APPLICATION_SITE]} ?? '';
                else $this->_userFolder = $authen->user_code ?? $authen->admin_code ?? '';
            }else $this->_userFolder = '';
            unset($configs, $authen);
        }
    }
    
    public function __invoke( ) {
        //$authen = $this->view->zfAuthen();
        //$this->_userFolder = $authen->user_code ?? $authen->admin_code ?? '';
        return $this;
    }
    
    /**
     * Create CSRF token
     * @param string $userFolder
     * @param array $unique
     * @return string
     */
    public function generalCsrfToken( $unique = [], $userFolder = null ){
        $userFolder = $userFolder ?? $this->_userFolder;
        return self::$csrfToken->generalCsrfToken($userFolder, $unique);
    }
}
?>