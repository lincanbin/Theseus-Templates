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
    private $parse;

    public function __construct()
    {
        $this->parse = new TheseusParse();
    }

    public function display($fileName)
    {
        $this->parse->parse($fileName);
        //var_dump($this->parse);
        $componentBuildUpList = $this->getComponentBuildUpList();
        //var_dump($componentBuildUpList);
        $theseusDOM = new TheseusDOM($this->parse);
        foreach ($componentBuildUpList as $component) {
            $this->parse->component[$component]['buffer'] = $theseusDOM->buildUp($this->parse->component[$component]['buffer']);
        }
        $this->parse->templateBuffer = $theseusDOM->buildUp($this->parse->templateBuffer);
        var_dump($this->parse);

        //echo ($this->parse->templateBuffer);
    }

    public function getComponentBuildUpList()
    {
        $list = [];
        $keyGenerator = [];
        for ($i = 0; $i <= $this->parse->maxDepth; $i++) {
            $keyGenerator[$i] = $i * 100;
        }

        foreach ($this->parse->component as $name => $cp) {
            $depth = $cp['depth'];
            $list[$keyGenerator[$depth]] = $name;
            $keyGenerator[$depth]++;
        }
        krsort($list);
        return $list;
    }
}