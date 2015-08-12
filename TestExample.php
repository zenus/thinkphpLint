<?php
require_once __DIR__ . "/lintTest.php";
/**
 * Created by PhpStorm.
 * User: lb@lansee.net
 * Date: 2015/8/7
 * Time: 9:46
 */
class TestExample
{

    public  function  Test()
    {
        $Test = new lintTest('i love you');
        $Test->Welcome('i love you');
    }
}

 $lin =  new TestExample();
 $lin->Test();