<?php

namespace Zf\Ext\Model;

/**
 * "JSON_OBJECT" "(" { string "," NewValue }* ")"
 */
class JsonObject extends MysqlJsonFunctionNode
{
	const FUNCTION_NAME = 'JSON_OBJECT';

    /** @var string[] */
    protected $optionalArgumentTypes = [self::STRING_ARG, self::VALUE_ARG];

    /** @var bool */
    protected $allowOptionalArgumentRepeat = true;
}