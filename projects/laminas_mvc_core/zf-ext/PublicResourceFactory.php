<?php
namespace Zf\Ext;

use Interop\Container\ContainerInterface;
use Zf\Ext\Resource;

class PublicResourceFactory

{

    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        if ( isset($config['public_resource']) ){
            $rsConfig = $config['public_resource']; unset($config);
            return new PublicResource($rsConfig);
        }else unset($config);
        
        // Next middleware
        return (function ($request, $response, $next = null){
            if ($next) { return $next($request, $response); }
            return $response;
        });
    }
}