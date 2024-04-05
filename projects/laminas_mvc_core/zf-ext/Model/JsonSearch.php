<?php
namespace Zf\Ext\Model;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;

/**
 * @author Anh.Cao
 */
class JsonSearch extends FunctionNode
{
    public $json = null;
    
    public $mode = null;

    public $search = null;
    
    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->json = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->mode = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->search = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
    
    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return 'JSON_SEARCH(' .
            $this->json->dispatch($sqlWalker) . ', ' .
            $this->mode->dispatch($sqlWalker) . ', ' .
            $this->search->dispatch($sqlWalker) .
        ')';
    }
}
