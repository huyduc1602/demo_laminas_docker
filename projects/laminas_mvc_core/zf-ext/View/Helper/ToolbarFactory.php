<?php
/**
 * @link      http://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright Copyright (c) 2015-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zf\Ext\View\Helper;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
/**
 * Factory for instantiating classes with no dependencies or which accept a single array.
 *
 * The InvokableFactory can be used for any class that:
 *
 * - has no constructor arguments;
 * - accepts a single array of arguments via the constructor.
 *
 * It replaces the "invokables" and "invokable class" functionality of the v2
 * service manager.
 */
class ToolbarFactory implements FactoryInterface
{
    public $requestedName ='bootstrapToolbar';
    /**
     * {@inheritDoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $names = explode('\\', $requestedName);
        $this->requestedName = array_pop($names);
        $this->requestedName = lcfirst($this->requestedName);
        
        $request = $container->get('request') ?? null;
        $router = $container->get('router') ?? null;
        
        return new $requestedName($request, $router);
    }
    
    /**
     *
     * @param ServiceLocatorInterface $container
     * @return LoaderPluginManager
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, $this->requestedName);
    }
}
