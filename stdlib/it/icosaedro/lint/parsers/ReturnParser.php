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
use it\icosaedro\lint\statements\ReturnStatement;
use it\icosaedro\lint\statements\Statement;

class ReturnParser implements Parser
{

    /**
     * @param Globals $globals
     * @return int
     */
    public function parse(Globals $globals)
    {
        ReturnStatement::parse($globals);
        Statement::parseStatementTerminator($globals);
        return Flow::RETURN_MASK;
    }
}