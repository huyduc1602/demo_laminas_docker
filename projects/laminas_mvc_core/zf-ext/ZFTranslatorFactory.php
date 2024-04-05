<?php
namespace Zf\Ext;

use Interop\Container\ContainerInterface;
use Zf\Ext\ZFTranslator;
use \Laminas\I18n\Translator;
class ZFTranslatorFactory
{
    public function __invoke(ContainerInterface $container)
    {
        if ( $container->has(Translator\TranslatorInterface::class) ) {
            return new ZFTranslator($container->get(Translator\TranslatorInterface::class));
        }
        
        // Next middleware
        return (function ($request, $response, $next = null) {
            if ($next) {
                return $next($request, $response);
            }
            return $response;
        });
    }
}
?>