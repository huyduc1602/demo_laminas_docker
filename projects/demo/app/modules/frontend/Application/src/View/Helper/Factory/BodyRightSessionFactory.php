<?php
namespace Application\View\Helper\Factory;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Application\View\Helper\BodyRightSession;
/**
 * This is the factory for Menu view helper. Its purpose is to instantiate the
 * helper and init menu items.
 */
class BodyRightSessionFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $controller = $container->get('ControllerPluginManager');
        $entityManager = $controller->get('getEntityManager');
        unset($controller);
        
        $zfUrl = $container->get('ViewHelperManager')->get('zfUrl');
        // Instantiate the helper.
        return new BodyRightSession( $zfUrl, $entityManager() );
    }
}

