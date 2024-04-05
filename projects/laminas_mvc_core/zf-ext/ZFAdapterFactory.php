<?php
namespace Zf\Ext;
use Interop\Container\ContainerInterface;
use Zf\Ext\ZFAdapter;

class ZFAdapterFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        if ( isset($config['zf_db']) ){
            return new ZFAdapter($config['zf_db']);
        }; unset($config);
        
        // Next middleware
        return (function ($request, $response, $next = null){
            if ($next) { return $next($request, $response); }
            return $response;
        });
    }
}
?>