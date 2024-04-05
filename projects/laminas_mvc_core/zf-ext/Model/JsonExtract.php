<?php
namespace Zf\Ext\Model;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;

/**
 * @author Mr.Phap
 */
class JsonExtract extends FunctionNode
{
    public $json = null;
    
    public $path = null;
    
    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->json = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->path = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
    
    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return 'JSON_EXTRACT(' .
            $this->json->dispatch($sqlWalker) . ', ' .
            $this->path->dispatch($sqlWalker) .
        ')';
    }
}
