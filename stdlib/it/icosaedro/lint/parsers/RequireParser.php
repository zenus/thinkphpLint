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

class RequireParser implements Parser
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
            $globals->logger->warning($scanner->here(), "`require' inside a function. Suggest `require_once' instead");
        }
        IncludeAndRequireStatements::parse($globals, "require");
        Statement::parseStatementTerminator($globals);
        return Flow::NEXT_MASK;
    }
}