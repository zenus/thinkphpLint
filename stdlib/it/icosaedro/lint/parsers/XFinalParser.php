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
use it\icosaedro\lint\statements\ClassStatement;

class XFinalParser implements Parser
{

    /**
     * @param Globals $globals
     * @return int
     */
    public function parse(Globals $globals)
    {
        ClassStatement::parse($globals, FALSE);
        return Flow::NEXT_MASK;
    }
}