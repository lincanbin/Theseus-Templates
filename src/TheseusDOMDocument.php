<?php
/**
 * Created by PhpStorm.
 * User: lincanbin
 * Date: 2017/7/26
 * Time: 14:23
 */

namespace lincanbin;

use \DOMDocument;
use \DOMElement;


class TheseusDOMDocument
{
    public $dom;
    public $domDocument;
    public $TEMP_CONTENT;
    public $PARENT_TAG_NAME;
    public $isLayout;


    public function __construct()
    {
        if (!$this->dom) {
            $this->dom = new DOMDocument('1.0', 'UTF-8');
        }
        $this->dom->preserveWhiteSpace = true;
        $this->dom->formatOutput = false;
        $this->TEMP_CONTENT = 'a7c598c8-fcb7-4bde-af9c-91c6515fbf7a-lincanbin-' . md5(mt_rand());
        $this->PARENT_TAG_NAME = substr('tag' . md5(mt_rand()), 0, 8);
        //Disable libxml errors
        libxml_use_internal_errors(true);
        return $this->dom;
    }

    public function load($html)
    {
        //var_dump($this->isLayout);
        $html = str_replace(chr(13), '', $html);
        $html = '<?xml version="1.0" encoding="utf-8" ?><' . $this->PARENT_TAG_NAME . '>' . $html . '</' . $this->PARENT_TAG_NAME . '>';

        $this->dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    }

    /**
     * Output the result
     * @return string HTML string
     */
    public function save()
    {
        $result = '';
        if (!is_null($this->dom)) {
            //SaveXML : <br/><img/>
            //SaveHTML: <br><img>
            $result = trim($this->dom->saveXML($this->getRealElement()));
            $result = str_replace($this->TEMP_CONTENT, '', $result);
            $parentTagNameLength = strlen($this->PARENT_TAG_NAME);
            $result = substr($result, $parentTagNameLength + 2, -($parentTagNameLength + 3));
        }
        return $result;
    }

    /**
     * Get Element without doc type.
     * @return DOMElement
     */
    public function getRealElement()
    {
        return $this->dom->getElementsByTagName($this->PARENT_TAG_NAME)->item(0);
    }
}