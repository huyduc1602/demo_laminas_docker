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

/**
 * @todo       allow specifying status code as a default, or as an option to methods
 */
class ZendDb extends AbstractPlugin
{
    const SERVICE_ALIAS = 'getZendDbAdapter';
    protected $adapter = null;
    public function __construct(ContainerInterface $container){
        $this->adapter = $container->get('Laminas\Db\Adapter\Adapter');
    }
    
    public function __invoke() {
        return $this->adapter;
    }
}
