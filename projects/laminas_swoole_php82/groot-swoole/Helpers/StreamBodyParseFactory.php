<?php
namespace GrootSwoole\Helpers;

use GrootSwoole\Helpers\StreamBodyParse;
use Mezzio\Helper\BodyParams\BodyParamsMiddleware;
use Mezzio\Helper\BodyParams\FormUrlEncodedStrategy;
use Interop\Container\ContainerInterface;

class StreamBodyParseFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $bodyParams = new BodyParamsMiddleware();
        $bodyParams->clearStrategies();
        $bodyParams->addStrategy(new StreamBodyParse());
        $bodyParams->addStrategy(new FormUrlEncodedStrategy());
        return $bodyParams;
    }
}

