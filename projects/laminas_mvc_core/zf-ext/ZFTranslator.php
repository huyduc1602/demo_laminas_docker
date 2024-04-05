<?php
namespace Zf\Ext;

//use Locale;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\I18n\Translator\Translator;

class ZFTranslator
{
    /**
     * Key to get Translator
     * @var string
     */
    const TRANSLATOR_KEY = 'ZF_Translator';
    
    /**
     * Translator
     * @var Laminas\I18n\Translator\Translator
     */
    public static $_translator = null;

    public function __construct(Translator $translator)
    {
        self::$_translator = $translator;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        if ( $next ) {
            return $next(
                $request->withAttribute(self::TRANSLATOR_KEY, self::$_translator), 
                $response
            );
        }
        return $response;
    }
}
?>