<?php

namespace Zf\Ext\Model;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;

class MatchAgainstFull extends AbstractFunctionNode
{
    /** @var array list of \Doctrine\ORM\Query\AST\PathExpression */
    protected $pathExp = null;

    /** @var string */
    protected $against = null;

    /** @var bool */
    protected $booleanMode = false;

    /** @var bool */
    protected $queryExpansion = false;
	/** @var bool */
	protected $queryNatural = false;

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        // match
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        // first Path Expression is mandatory
        $this->pathExp = [];
        $this->pathExp[] = $parser->StateFieldPathExpression();

        // Subsequent Path Expressions are optional
        $lexer = $parser->getLexer();
        while ($lexer->isNextToken(Lexer::T_COMMA)) {
            $parser->match(Lexer::T_COMMA);
            $this->pathExp[] = $parser->StateFieldPathExpression();
        }

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);

        // against
        if ( $this->getLexerVal($lexer->lookahead) !== 'against') {
            $parser->syntaxError('against');
        }

        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->against = $parser->StringPrimary();

        if ( $this->getLexerVal($lexer->lookahead) === 'boolean') {
            $parser->match(Lexer::T_IDENTIFIER);
            $this->booleanMode = true;
        }

        if ( $this->getLexerVal($lexer->lookahead) === 'expand') {
            $parser->match(Lexer::T_IDENTIFIER);
            $this->queryExpansion = true;
        }
		if ( $this->getLexerVal($lexer->lookahead) === 'natural') {
            $parser->match(Lexer::T_IDENTIFIER);
            $this->queryNatural = true;
        }
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(\Doctrine\ORM\Query\SqlWalker $walker)
    {
        $fields = [];

        foreach ($this->pathExp as $pathExp) {
            $fields[] = $pathExp->dispatch($walker);
        }

        $against = $walker->walkStringPrimary($this->against)
        . ($this->booleanMode ? ' IN BOOLEAN MODE' : '')
        . ($this->queryExpansion ? ' WITH QUERY EXPANSION' : '')
		. ($this->queryNatural ? ' IN NATURAL LANGUAGE MODE' : '')
		;

        return sprintf('MATCH (%s) AGAINST (%s)', implode(', ', $fields), $against);
    }
}
