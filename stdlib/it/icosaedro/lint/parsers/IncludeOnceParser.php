<?php
/**
 * Created by PhpStorm.
 * User: zenus
 * Date: 2015/8/7
 * Time: 14:53
 */

namespace it\icosaedro\lint\parsers;


use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Flow;
use it\icosaedro\lint\statements\IncludeAndRequireStatements;
use it\icosaedro\lint\statements\Statement;

class IncludeOnceParser implements Parser
{

    /**
     * @param Globals $globals
     * @return int
     */
    public function parse(Globals $globals)
    {
        IncludeAndRequireStatements::parse($globals, "include_once");
        Statement::parseStatementTerminator($globals);
        return Flow::NEXT_MASK;
    }
}