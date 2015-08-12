<?php
/**
 * Created by PhpStorm.
 * User: lb@lansee.net
 * Date: 2015/8/7
 * Time: 14:53
 */

namespace it\icosaedro\lint\parsers;


use it\icosaedro\lint\Globals;
use it\icosaedro\lint\Flow;

class OpenTagParser implements Parser
{

    /**
     * @param Globals $globals
     * @return int
     */
    public function parse(Globals $globals)
    {
        // TODO: Implement parse() method.
        $pkg = $globals->curr_pkg;
        $scanner = $pkg->scanner;
        return Flow::NEXT_MASK;
    }
}