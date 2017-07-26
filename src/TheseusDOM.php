<?php
/**
 * Created by PhpStorm.
 * User: lincanbin
 * Date: 2017/7/25
 * Time: 14:44
 */

namespace lincanbin;

use \Exception;
use \DOMDocument;
use \DOMElement;
use \DOMNode;

class TheseusDOM
{
    private $dom;
    public $parse;
    /**
     * The empty elements in HTML
     * https://developer.mozilla.org/en-US/docs/Glossary/Empty_element
     */
    protected $emptyElementList = array(
        'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', //'keygen',
        'link', 'meta', 'param', 'source', 'track', 'wbr',
        'stack'
    );

    public function __construct(TheseusParse $parse)
    {
        if (extension_loaded("dom") === false) {
            throw new Exception('DOM extension is required. http://php.net/manual/en/dom.installation.php');
        }
        $this->parse = $parse;
        if (!$this->dom) {
            $this->dom = new TheseusDOMDocument();
        }
    }

    public function buildUp($componentHTML)
    {
        $this->dom->load($componentHTML);
        $elem = $this->dom->getRealElement();
        if (is_null($elem)) {
            return '';
        }
        $this->traverseNodes($elem);

        $result = $this->dom->save();
        //var_dump($result);
        //echo "\n\n\n\n";
        var_dump($this->dom->dom->doctype);
        return $result;
    }

    public function traverseNodes(DOMElement $elem)
    {
        $result = false; //是否有替换节点，默认为否
        $nodeName = $elem->nodeName;
        $textContent = $elem->textContent;
        //var_dump($nodeName);
        if (array_key_exists($nodeName, $this->parse->component)) {

            //var_dump($this->dom->dom->saveHTML($elem));
            //echo "\n\n\n";
            $newElementHTML = str_ireplace('@yield', $this->getInnerHTML($elem), $this->parse->component[$nodeName]['buffer']);
            $result = true;
            $this->replaceNodeWithHtml($elem, $newElementHTML);
            //var_dump($newElementHTML);
        }
        if ($elem->hasChildNodes()) {
            $children = $elem->childNodes;
            $index = 0;
            while ($index < $children->length) {
                $cleanNode = $children->item($index);// DOMElement or DOMText
                $hasReplaced = false;
                if ($cleanNode instanceof DOMElement) {
                    $hasReplaced = $this->traverseNodes($cleanNode);
                }
                if ($hasReplaced === false) {
                    $index++;
                }
            }
        } else {
            if (!in_array($nodeName, $this->emptyElementList) && !$this->isValidText($textContent)) {
                $elem->nodeValue = $this->dom->TEMP_CONTENT;
            }
        }
        return $result;
    }

    public function replaceNodeWithHtml(DOMElement $oldNode, $newNodeHtml)
    {
        $dom = new TheseusDOMDocument();
        $dom->load($newNodeHtml);
        $children = $dom->getRealElement()->childNodes;
        /**
         * @var DOMNode $child
         */
        foreach ($children as $child) {
            //var_dump($child);
            $importedNode = $oldNode->ownerDocument->importNode($child, true);
            //var_dump($importedNode);
            $oldNode->parentNode->insertBefore($importedNode, $oldNode);
        }
        $oldNode->parentNode->removeChild($oldNode);
    }

    public function getInnerHTML(DOMNode $element)
    {
        $innerHTML = "";
        $children = $element->childNodes;
        /**
         * @var DOMNode $child
         */
        foreach ($children as $child) {
            if (!in_array($child->nodeName, $this->emptyElementList) && !$this->isValidText($child->textContent)) {
                $child->nodeValue = $this->dom->TEMP_CONTENT;
            }
            $innerHTML .= $element->ownerDocument->saveHTML($child);
        }

        return $innerHTML;
    }

    /**
     * Check if there is a valid text in the tag
     * @param string $string
     * @return boolean Whether there is valid text
     */
    private function isValidText($string)
    {
        $search = array(" ", "　", "\n", "\r", "\t");
        $replace = array("", "", "", "", "");
        return str_replace($search, $replace, $string) !== '';
    }
}