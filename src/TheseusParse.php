<?php

namespace lincanbin;

use \Exception;

class TheseusParse
{
    private $componentPointer;
    public $maxDepth;
    public $filePath;
    public $component;
    public $stack;
    public $data;
    public $templateBuffer;
    const END_TAG = [
        '@endcomponent',
        '@endpush',
        '@enddata'
    ];


    public function parse($fileName)
    {
        $this->maxDepth = 0;
        $this->filePath = dirname($fileName);
        $this->component = [];
        $this->componentPointer = null;
        $this->stack = [];
        $this->data = '';
        $this->templateBuffer = '';
        $this->_parse($fileName, 0);
    }

    private function _parse($fileName, $depth)
    {
        $this->maxDepth = max($this->maxDepth, $depth);
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
        } else if (preg_match("/^@component\([\'\"]([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)[\'\"](,\s?[\'\"]([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)[\'\"])?\)$/i", $line, $componentParameters) > 0) {
            if (is_null($this->componentPointer) === false) {
                throw new Exception("Missing @endcomponent or @endpush
                in " . $fileName . ", Unclosed component " . $this->componentPointer[1] . ".");
            }
            //var_dump($componentParameters);
            $componentName = strtolower($componentParameters[1]);
            $itemName = !empty($componentParameters[3]) ? $componentParameters[3] : '';
            if (empty($this->component[$componentName])) {
                $this->component[$componentName] = [
                    'type'     => 'component',
                    'buffer'   => '',
                    'depth'    => $depth,
                    'itemName' => $itemName
                ];
            } else {
                $this->component[$componentName]['depth'] = $depth;
            }
            $this->componentPointer = ['component', $componentName];
        } else if (preg_match("/^@push\([\'\"](.*)[\'\"]\)$/i", $line, $stackParameter) > 0) {
            if (is_null($this->componentPointer) === false) {
                throw new Exception("Missing @endcomponent or @endpush 
                in " . $fileName . ", Unclosed component " . $this->componentPointer[1] . ".");
            }
            $stackName = $stackParameter[1];
            $this->componentPointer = ['stack', $stackName];
        } else if (preg_match("/^@data$/i", $line, $stackParameter) > 0) {
            if (is_null($this->componentPointer) === false) {
                throw new Exception("Missing @endcomponent or @endpush 
                in " . $fileName . ", Unclosed component " . $this->componentPointer[1] . ".");
            }
            $this->componentPointer = ['data', ''];
        } else if (in_array($line, self::END_TAG)) {
            $this->componentPointer = null;
        } else {
            if (is_null($this->componentPointer) === true) {
                $this->templateBuffer .= $line . "\n";
            } else {
                $type = $this->componentPointer[0];
                $name = $this->componentPointer[1];
                if ($type === 'component') {
                    $this->component[$name]['buffer'] .= $line . "\n";
                } else if ($type === 'stack') {
                    $this->stack[$name][] = $line;
                } else if ($type === 'data') {
                    $this->data .= $line . "\n";
                }
            }
        }
        return false;
    }
}