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
use it\icosaedro\lint\statements\DoWhileStatement;
use it\icosaedro\lint\statements\Statement;

class DoParser implements Parser
{

    /**
     * @param Globals $globals
     * @return int
     */
    public function parse(Globals $globals)
    {
        $res = DoWhileStatement::parse($globals);
        Statement::parseStatementTerminator($globals);
        return $res;
    }
}