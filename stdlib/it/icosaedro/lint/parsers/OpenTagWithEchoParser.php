<?php
/**
 * Created by PhpStorm.
 * User: zenus
 * Date: 2015/8/7
 * Time: 14:57
 */

namespace it\icosaedro\lint\parsers;

use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Flow;
use it\icosaedro\lint\statements\EchoBlockStatement;

class OpenTagWithEchoParser implements Parser
{

    /**
     * @param Globals $globals
     * @return int
     * @throws \it\icosaedro\lint\ParseException
     */
    public function parse(Globals $globals)
    {
        EchoBlockStatement::parse($globals);
        return Flow::NEXT_MASK;
        // TODO: Implement parse() method.
    }
}