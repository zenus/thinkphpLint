<?php

require_once __DIR__ . "/TopTest.php";
/**
 * Created by PhpStorm.
 * User: lb@lansee.net
 * Date: 2015/8/7
 * Time: 9:37
 */
class lintTest
{

    /**
     * @var int
     */
    private $No = 0;
    /**
     * @param int $No
     * @return void
     */

    public function  __construct($No)
    {
        $this->No = $No;
    }


    /**
     * @param string $word
     * @return void
     */
    public function Welcome($word)
    {
        $top = new TopTest();
        echo 'This is'.$top->Top(1).' the '.$this->No.'times you said '.$word.'to me';
    }

}