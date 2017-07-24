<?php

namespace lincanbin;

use \Exception;

class TheseusParse
{
    public $filePath;
    public $component;
    public $stack;
    private $tempComponentStack;
    public $templateBuffer;


    public function parse($fileName)
    {
        $this->filePath = dirname($fileName);
        $this->component = [];
        $this->_parse($fileName, true);
    }

    private function _parse($fileName, $isRootTemplate = false)
    {
        $handle = @fopen($fileName, "r");
        if ($handle !== false) {
            while (($line = fgets($handle, 4096)) !== false) {
                $this->handleLine(trim($line), $fileName, $isRootTemplate);
            }
            if (feof($handle) !== true) {
                throw new Exception("Error: unexpected fgets() fail");
            }
            fclose($handle);
        } else {
            throw new Exception("Error: open " . $fileName . " fail");
        }
    }


    public function handleLine($line, $fileName, $isRootTemplate = false)
    {
        if (preg_match("/^@import\([\'\"](.*)[\'\"]\)$/i", $line, $importFileName) > 0) {
            $componentName = $importFileName[1];
            if ($isRootTemplate) {
                $this->component[$componentName] = '';
            } else {
                $this->tempComponentStack[$componentName] = '';
            }
            //var_dump($importFileName);
            $this->_parse(dirname($fileName) . '/' . $componentName . 'Component.tss');
        }
    }
}