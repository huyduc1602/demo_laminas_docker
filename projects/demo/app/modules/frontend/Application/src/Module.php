<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Laminas\Mvc\MvcEvent;
use Laminas\Session\SessionManager;
use Laminas\Mvc\Controller\AbstractActionController;
class Module
{
    const VERSION = '3.0.0dev';

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
    
    /**
     * This method is called once the MVC bootstrapping is complete. 
     */
    public function onBootstrap(MvcEvent $event)
    {
        $application = $event->getApplication();
        $serviceManager = $application->getServiceManager();
        
        // The following line instantiates the SessionManager and automatically
        // makes the SessionManager the 'default' one to avoid passing the
        // session manager as a dependency to other models.
        $sessionManager = $serviceManager->get(SessionManager::class);
        
        // Get event manager.
        $eventManager = $event->getApplication()->getEventManager();
        $sharedEventManager = $eventManager->getSharedManager();
        // Register the event listener method. 
        $sharedEventManager->attach(AbstractActionController::class, 
                MvcEvent::EVENT_DISPATCH, [$this, 'onDispatch'], 100);
    }
    
    /**
     * Event listener method for the 'Dispatch' event. We listen to the Dispatch
     * event to call the access filter. The access filter allows to determine if
     * the current visitor is allowed to see the page or not. If he/she
     * is not authorized and is not allowed to see the page, we redirect the user
     * to the login page.
     */
    public function onDispatch(MvcEvent $event)
    {
        // Get controller and action to which the HTTP request was dispatched.
        $controller = $event->getTarget();
        $routeMatch = $event->getRouteMatch();
    
        $controllerName = $routeMatch->getParam('controller', null);
        $actionName = $routeMatch->getParam('action', null);
    
        // Convert dash-style action name to camel-case.
        $actionName = str_replace('-', '', lcfirst(ucwords($actionName, '-')));
        $routeName = $routeMatch->getMatchedRouteName();
        if( 'index' == $actionName ){
            $rs = $this->_saveQueryParams([
                'routeName' => $routeName,
                'actionName' => $actionName
            ], $controller->params()->fromQuery());
        }
    }
    
    /**
     * save query params
     * @param array $opts
     * @param array $params
     */
    protected function _saveQueryParams( $opts = [], $params = [] ){
        $key = str_replace(['-', '/'], '_', implode('_', $opts));
    
        $paramsSession = new \Laminas\Session\Container($key);
        $paramsSession->offsetSet('queryString', (array)$params);
        unset($paramsSession);
    }
    
    /**
     * get query params
     * @param array $opts
     * @param bool $unsetData
     */
    public function getIndexQueryParams( $opts = [], $unsetData = true ){
        // -- get query string from session
        $key = str_replace(['-', '/'], '_', implode('_', $opts));
    
        $paramsSession = new \Laminas\Session\Container($key);
        $params = $paramsSession->offsetGet('queryString');
    
        // -- Destroy session
        if ( true === $unsetData ) $paramsSession->offsetUnset('queryString');
    
        unset($paramsSession);
        return $params;
    }
}

