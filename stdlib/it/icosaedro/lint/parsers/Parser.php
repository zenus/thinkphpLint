<?php
namespace it\icosaedro\lint\parsers;
require_once __DIR__ . "/../../../../all.php";
use it\icosaedro\lint\Globals;

/**
 * Created by PhpStorm.
 * User: lb@lansee.net
 * Date: 2015/8/7
 * Time: 14:38
 */
interface Parser
{
    public function parse(Globals $globals);
}