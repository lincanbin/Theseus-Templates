<?php

namespace lincanbin;

use \Exception;

class TheseusParse
{
    public $filePath;
    public $component;
    private $blockName;
    public $templateBuffer;


    public function parse($fileName)
    {
        $this->filePath = dirname($fileName);
        $this->_parse($fileName);
    }

    private function _parse($fileName)
    {
        $handle = @fopen($fileName, "r");
        if ($handle !== false) {
            while (($line = fgets($handle, 4096)) !== false) {
                $this->handleLine(trim($line), $fileName);
            }
            if (feof($handle) !== true) {
                throw new Exception("Error: unexpected fgets() fail");
            }
            fclose($handle);
        } else {
            throw new Exception("Error: open " . $fileName . " fail");
        }
    }


    public function handleLine($line, $fileName){
        if (preg_match("/^@import\([\'\"](.*)[\'\"]\)$/i", $line, $importFileName) > 0) {
            //var_dump($importFileName);
            $this->_parse(dirname($fileName) . '/' . $importFileName[1] . 'Component.tss');
        }
    }
}