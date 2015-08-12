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

class XDocBlockParser implements Parser
{

    /**
     * @param Globals $globals
     * @return int
     */
    public function parse(Globals $globals)
    {
		$pkg = $globals->curr_pkg;
		$scanner = $pkg->scanner;
        if( $globals->parse_phpdoc )
            $globals->logger->error($scanner->here(), "invalid scope for documentation");
        $scanner->readSym();
        return Flow::NEXT_MASK;
    }
}