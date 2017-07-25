<?php
/**
 * Created by PhpStorm.
 * User: lincanbin
 * Date: 2017/7/25
 * Time: 10:57
 */

namespace lincanbin;

class Theseus
{
    public function display($fileName)
    {
        $parse = new TheseusParse();
        $parse->parse($fileName);
        var_dump($parse);
    }
}