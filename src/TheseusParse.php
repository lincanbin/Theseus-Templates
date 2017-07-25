<?php

namespace lincanbin;

use \Exception;

class TheseusParse
{
    public $filePath;
    public $component;
    private $componentPointer;
    public $stack;
    public $data;
    public $templateBuffer;
    const END_TAG = [
        '@endcomponent',
        '@endeach',
        '@endpush',
        '@enddata'
    ];


    public function parse($fileName)
    {
        $this->filePath = dirname($fileName);
        $this->component = [];
        $this->componentPointer = null;
        $this->stack = [];
        $this->templateBuffer = '';
        $this->_parse($fileName, 0);
    }

    private function _parse($fileName, $depth)
    {
        $handle = @fopen($fileName, "r");
        if ($handle !== false) {
            while (($line = fgets($handle, 4096)) !== false) {
                $this->handleLine(trim($line), $fileName, $depth);
            }
            if (feof($handle) !== true) {
                throw new Exception("Error: unexpected fgets() fail");
            }
            fclose($handle);
        } else {
            throw new Exception("Error: open " . $fileName . " fail");
        }
    }


    public function handleLine($line, $fileName, $depth)
    {
        //var_dump($fileName . $line);
        if (empty($line)) {
            return false;
        } else if (preg_match("/^@import\([\'\"](.*)[\'\"]\)$/i", $line, $importFileName) > 0) {
            $componentName = $importFileName[1];

            //var_dump($importFileName);
            $this->_parse(dirname($fileName) . '/' . $componentName . 'Component.tss', ++$depth);
        } else if (preg_match("/^@component\([\'\"](.*)[\'\"]\)$/i", $line, $componentParameters) > 0) {
            if (is_null($this->componentPointer) === false) {
                throw new Exception("Missing @endcomponent or @endeach or @endpush
                in " . $fileName . ", Unclosed component " . $this->componentPointer[1] . ".");
            }
            $componentName = $componentParameters[1];
            $this->component[$componentName] = [
                'type'     => 'component',
                'buffer'   => '',
                'depth'    => $depth,
                'itemName' => ''
            ];
            $this->componentPointer = ['component', $componentName];
        } else if (preg_match("/^@each\([\'\"](.*)[\'\"],\s?[\'\"](.*)[\'\"]\)$/i", $line, $eachParameters) > 0) {
            if (is_null($this->componentPointer) === false) {
                throw new Exception("Missing @endcomponent or @endeach or @endpush
                in " . $fileName . ", Unclosed component " . $this->componentPointer[1] . ".");
            }
            $eachName = $eachParameters[1];
            $itemName = $eachParameters[2];
            $this->component[$eachName] = [
                'type'     => 'each',
                'buffer'   => '',
                'depth'    => $depth,
                'itemName' => $itemName
            ];
            $this->componentPointer = ['component', $eachName];

        } else if (preg_match("/^@push\([\'\"](.*)[\'\"]\)$/i", $line, $stackParameter) > 0) {
            if (is_null($this->componentPointer) === false) {
                throw new Exception("Missing @endcomponent or @endeach or @endpush 
                in " . $fileName . ", Unclosed component " . $this->componentPointer[1] . ".");
            }
            $stackName = $stackParameter[1];
            $this->componentPointer = ['stack', $stackName];
        } else if (preg_match("/^@data$/i", $line, $stackParameter) > 0) {
            if (is_null($this->componentPointer) === false) {
                throw new Exception("Missing @endcomponent or @endeach or @endpush 
                in " . $fileName . ", Unclosed component " . $this->componentPointer[1] . ".");
            }
            $this->componentPointer = ['data', ''];
        } else if (in_array($line, self::END_TAG)) {
            $this->componentPointer = null;
        } else {
            if (is_null($this->componentPointer) === true) {
                $this->templateBuffer .= $line;
            } else {
                $type = $this->componentPointer[0];
                $name = $this->componentPointer[1];
                if ($type === 'component') {
                    $this->component[$name]['buffer'] .= $line;
                } else if ($type === 'stack') {
                    $this->stack[$name][] = $line;
                } else if ($type === 'data') {
                    $this->data .= $line;
                }
            }
        }
        return false;
    }
}