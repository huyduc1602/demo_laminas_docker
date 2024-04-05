<?php
namespace Application\View\Helper\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Application\View\Helper\HeaderSession;
use Laminas\Authentication\AuthenticationService;
/**
 * This is the factory for Menu view helper. Its purpose is to instantiate the
 * helper and init menu items.
 */
class HeaderSessionFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if ($container->has(AuthenticationService::class)){
            $authen = $container->get(AuthenticationService::class)->getIdentity();
        }else $authen = null;
        
        $controller = $container->get('ControllerPluginManager');
        $entityManager = $controller->get('getEntityManager');
        $router = $container->get('router');
        $request = $container->get('request');
        
        // Get the router match
        $routerMatch = $router->match($request);
        unset($controller, $request, $router);
        $configs = $container->get('config');
        // Instantiate the helper.
        return new HeaderSession( 
            $entityManager(), $authen, $configs['facebook'] ?? [], $routerMatch
        );
    }
}

