<?php
namespace Zf\Ext\Helper;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class NotFoundErrorHandle
{
    /**
     * @param array $contentTypes
     * @return bool
     */
    public function match($contentTypes = array())
    {
        foreach ($contentTypes as $contentType){
            $parts = explode(';', $contentType);
            $mime = array_shift($parts);
            $match = (bool) preg_match('/(application\/json)/i', trim($mime));
            if( true ===  $match ) return true;
        }
        return false;
    }
    
    public function __invoke(Request $req, Response $res, $next)
    {
        if ( false == $this->match($req->getHeader('Content-Type')) && $next ) 
            return $next($req, $res);
        
        // Other things can be done here; e.g., logging
        return $next($req, $res->withStatus(404), 'Page Not Found');
    }
}
?>