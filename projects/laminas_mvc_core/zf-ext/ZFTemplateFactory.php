<?php
namespace Zf\Ext;

use Interop\Container\ContainerInterface;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use Zf\Ext\ZFTemplate;

class ZFTemplateFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new ZFTemplate(
            $container->get(RouterInterface::class), 
            ($container->has(TemplateRendererInterface::class))
            ? $container->get(TemplateRendererInterface::class)
            : null
        );
    }
}
?>