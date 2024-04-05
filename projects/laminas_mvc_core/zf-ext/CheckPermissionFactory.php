<?php
namespace Zf\Ext;

use Interop\Container\ContainerInterface;
use Zf\Ext\CheckPermission;

class CheckPermissionFactory

{

    public function __invoke(ContainerInterface $container )
    {
        $config = $container->get('config');
        // Neu co cau hinh phan quyen
        if ( isset($config['zf_permission']) ){
            $pConfigs = array_merge(
                // Group
                $config['zf_permission'],
                // Permission
                ['routes' => (array)$config['routes']]
            );
            
            unset($config);
            return new CheckPermission($pConfigs);
        }; unset($config);
        
        // Next middleware
        return (function ($request, $response, $next = null){
            if ($next) { return $next($request, $response); }
            return $response;
        });
    }
}