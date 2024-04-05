<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zf\Ext\Controller;

use Laminas\Http\Response;
use Laminas\Mvc\Exception;
use Laminas\Mvc\InjectApplicationEventInterface;

/**
 * @todo       allow specifying status code as a default, or as an option to methods
 */
class ZfRedirect extends \Laminas\Mvc\Controller\Plugin\Redirect
{
    protected $event;
    protected $response;
    
    /**
     * Get old query param of search form
     * @param array $params
     * @return array
     */
    protected function getOldQuery( array $params = [] ){
        $action = empty($params['action']) ? 'index' : $params['action'];
        
        // -- get query string from session
        $key = crc32(json_encode([$params['router'], $action]));
        $oldQuery = [];
        $container = new \Laminas\Session\Container("queryStringMn");
        
        $ssManager = $this->getEvent()->getApplication()->getServiceManager();
        if ($ssManager->has('Laminas\Session\SessionManager'))
            $container::setDefaultManager(
                $ssManager->get('Laminas\Session\SessionManager')
            );
        
        if ( $container->offsetExists('queryString') ){
            $oldQuery = $container->offsetGet('queryString');
            $container->offsetUnset('queryString');
        }
        unset($container);
        return $oldQuery;
    }
    /**
     * Generate redirect response based on given route
     *
     * @param  string $route RouteInterface name
     * @param  array $params Parameters to use in url generation, if any
     * @param  array $options RouteInterface-specific options to use in url generation, if any
     * @param  bool $reuseMatchedParams Whether to reuse matched parameters
     * @return Response
     * @throws Exception\DomainException if composed controller does not implement InjectApplicationEventInterface, or
     *         router cannot be found in controller event
     */
    public function toRoute($route = null, $params = [], $options = [], $reuseMatchedParams = false)
    {
        $controller = $this->getController();
        if (!$controller || !method_exists($controller, 'plugin')) {
            throw new Exception\DomainException('Redirect plugin requires a controller that defines the plugin() method');
        }

        $urlPlugin = $controller->plugin('url');
        $options['useOldQuery'] = $options['useOldQuery'] ?? false;
        if ( true === $options['useOldQuery'] ){
            $oldQuery = $this->getOldQuery(['router' => $route, 'action' => $params['action'] ?? '']);
            if ( $oldQuery ){
                $newQuery = array_replace($oldQuery, (array)($options['query'] ?? []));
                $options['query'] = $newQuery;
            }
            unset($options['userOldQuery']);
        }
        
        
        if (is_scalar($options)) {
            $url = $urlPlugin->fromRoute($route, $params, $options);
        } else {
            $url = $urlPlugin->fromRoute($route, $params, $options, $reuseMatchedParams);
        }
        $base = rtrim(BASE_URL, '/');
        $baseNew = ltrim($url, '/');
        $options['force_canonical'] = $options['force_canonical'] ?? false;
        if ( $options['force_canonical'] ){
            $url1 = $urlPlugin->fromRoute($route, $params);
            $url = str_replace($url1, $base . $url1, $baseNew);
        }else
            $url = implode('/', [ $base, $baseNew ]);
        return $this->toUrl($url);
    }
    /**
     * Return current route
     * @param  array $params Parameters to use in url generation, if any
     * @param  array $options RouteInterface-specific options to use in url generation, if any
     * @param  bool $reuseMatchedParams Whether to reuse matched parameters
     * @return \Laminas\Http\Response
     */
    public function toCurrentRoute($params = [], $options = [], $reuseMatchedParams = false){
        return $this->toRoute(
            $this->getController()->getCurrentRouteName(),
            $params, $options, $reuseMatchedParams
        );
    }
}
