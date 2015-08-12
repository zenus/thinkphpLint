<?php
/**
 * Created by PhpStorm.
 * User: lb@lansee.net
 * Date: 2015/8/7
 * Time: 14:58
 */

namespace it\icosaedro\lint\parsers;


use it\icosaedro\lint\Globals;
use it\icosaedro\lint\statements\CompoundStatement;

class LbraceParser implements Parser
{

    /**
     * @param Globals $globals
     * @return int
     */
    public function parse(Globals $globals)
    {
        return CompoundStatement::parse($globals);
    }
}
