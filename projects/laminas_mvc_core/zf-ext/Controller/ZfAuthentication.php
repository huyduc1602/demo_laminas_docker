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
class ZfAuthentication extends AbstractPlugin
{
    const SERVICE_ALIAS = 'getAuthen';
    /**
     * Zend authentication
     * @var AuthenticationService
     */
    protected $authen = null;
    
    public function __construct(ContainerInterface $container){
        if ( $container->has(AuthenticationService::class) ){
            
            $this->authen = $container->get(AuthenticationService::class)->getIdentity();
            if ($this->authen){
                $configs = $container->get('config');
                
                $authenConfig = $configs['zf_authen_key'] ?? [];
                $groupKey = ($configs['zf_permission'] ?? [])[APPLICATION_SITE]['user_key'] ?? 'user_groupcode';
                
                $this->authen->authen_key = $authenConfig[APPLICATION_SITE] ?? 'user_id';
                $this->authen->group_key = $groupKey;
                unset($configs);
            }
        }
    }
    
    public function __invoke( ) {
        return $this->authen;
    }
}
