<?php
namespace Zf\Ext;

use Psr\Container\ContainerInterface;

use Zf\Ext\CheckAuthen;

class CheckAuthenFactory

{

    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        if ( isset($config['zf_authen']) ){
            $auConfig = $config['zf_authen']; unset($config);
            return new CheckAuthen(
                $auConfig,
                $container->get(\Laminas\Session\SessionManager::class)
            );
        }else unset($config);
        
        // Next middleware
        return (function ($request, $response, $next = null){
            if ($next) { return $next($request, $response); }
            return $response;
        });
    }
}