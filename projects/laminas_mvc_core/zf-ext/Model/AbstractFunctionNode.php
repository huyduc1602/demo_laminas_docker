<?php

namespace Zf\Ext\Model;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;

abstract class AbstractFunctionNode extends FunctionNode
{
    /**
     * Get Value of token
     * @param $lookahead
     * @return string
     */
    protected function getLexerVal($lookahead){
        return strtolower(is_object($lookahead)
            ? ($lookahead->value ?? '')
            : ($lookahead['value'] ?? ''));
    }
}