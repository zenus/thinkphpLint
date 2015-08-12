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
use it\icosaedro\lint\Symbol;

class GotoParser implements Parser
{

    /**
     * @param Globals $globals
     * @return int
     */
    public function parse(Globals $globals)
    {
        $pkg = $globals->curr_pkg;
        $scanner = $pkg->scanner;
        $globals->logger->error($scanner->here(), "goto: unimplemented statement -- trying to continue anyway");
        $scanner->readSym();
        $globals->expect(Symbol::$sym_identifier, "expected identifier after `goto'");
        $scanner->readSym();
        $globals->expect(Symbol::$sym_semicolon, "expected `;'");
        $scanner->readSym();
        return Flow::RETURN_MASK;

    }
}