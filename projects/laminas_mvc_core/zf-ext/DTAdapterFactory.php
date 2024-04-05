<?php
namespace Zf\Ext;
use Interop\Container\ContainerInterface;
use Zf\Ext\DTAdapter;

class DTAdapterFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        if ( isset($config['doctrine']) ){
            return new DTAdapter($config['doctrine']);
        }; unset($config);
        
        // Next middleware
        return (function ($request, $response, $next = null){
            if ($next) { return $next($request, $response); }
            return $response;
        });
    }
}
?>