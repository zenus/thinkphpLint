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

class IncludeParser implements Parser
{

    /**
     * @param Globals $globals
     * @return int
     */
    public function parse(Globals $globals)
    {
        $pkg = $globals->curr_pkg;
        $scanner = $pkg->scanner;
        if( $pkg->scope > 0 ){
            $globals->logger->warning($scanner->here(), "include() inside a function. Suggest include_once() instead");
        }
        IncludeAndRequireStatements::parse($globals, "include");
        Statement::parseStatementTerminator($globals);
        return Flow::NEXT_MASK;
    }
}