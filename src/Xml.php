<?php

namespace Moccalotto\Exemel;

use LogicException;
use SimpleXmlElement;

class Xml
{
    /**
     * @var SimpleXmlElement
     */
    protected $root;

    public function __construct(SimpleXmlElement $root)
    {
        $this->root = $root;
    }

    protected function parsePathEntry($pathEntry)
    {
        // if a path is foo[bar] or foo[33], then return
        // ["foo", "bar"] or ["foo", 33];

        if (!preg_match('/([a-zA-Z0-9_:.-]*)\[(.*?)\]$/A', $pathEntry, $matches)) {
            return [$pathEntry, null];
        }

        return [
            $matches[1],  // elementName
            ctype_digit($matches[2]) ? intval($matches[2]) : $matches[2],  // elementIndex
        ];
    }

    protected function getOrCreateNextElement($root, $pathEntry)
    {
        list($elementName, $elementIndex) = $this->parsePathEntry($pathEntry);

        // index is '' - always add an element
        if ($elementIndex === '') {
            return $root->addChild($elementName);
        }

        // no index defined, add an element if one does not exist
        if ($elementIndex === null) {
            return isset($root->$elementName)
                ? $root->$elementName
                : $root->addChild($elementName);
        }

        // index defined, but does not exist - add it if possible
        if (!isset($root->$elementName[$elementIndex])) {
            $root->$elementName[$elementIndex] = null;
        }

        return $root->$elementName[$elementIndex];
    }

    protected function getNextElement($root, $pathEntry)
    {
        list($elementName, $elementIndex) = $this->parsePathEntry($pathEntry);

        // index is '' - always add an element
        if ($elementIndex === '') {
            throw new LogicException('Bad operator []');
        }

        // no index defined, add an element if one does not exist
        if ($elementIndex === null) {
            return isset($root->$elementName)
                ? $root->$elementName
                : null;
        }

        // index defined, but does not exist - add it if possible
        if (!isset($root->$elementName[$elementIndex])) {
            return null;
        }

        return $root->$elementName[$elementIndex];
    }

    public function root()
    {
        return $this->root;
    }

    public function formatted()
    {
        $doc = dom_import_simplexml($this->root)->ownerDocument;
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;
        $doc->encoding = 'UTF-8';

        return $doc->saveXML();
    }

    /**
     * Set a value on the xml doc.
     *
     * paths:   'foo/bar'               // set the contents of the <bar> element. add the baz element if needed
     *          'foo/bar[baz]'          // set the attribute "baz" on the element <bar>
     *          'foo/bar[]/ding'        // create a new <bar> element on foo - create a <ding> element on bar
     *                                     and set its contents
     *          '/foo/bar[3]/pong'      // on the fourth <bar> child of <foo>, set the contents of <pong>
     *          '/foo/bar/pong[]'       // on the fourth <bar> child of <foo>, set the contents of <pong>
     *
     * @param string                  $path
     * @param string|SimpleXmlElement $value
     *
     * @return $this
     */
    public function set($path, $value)
    {
        $entries = explode('/', $path);
        $lastEntry = array_pop($entries);
        reset($entries);

        // navigate to the right place in the xml - create entries ad hoc as needed
        $xml = $this->root;
        foreach ($entries as $pathEntry) {
            $xml = $this->getOrCreateNextElement($xml, $pathEntry);
        }

        list($elementName, $elementIndex) = $this->parsePathEntry($lastEntry);

        if ($elementIndex === '') {
            $xml->addChild($elementName, $value);

            return $this;
        }

        if ($elementIndex === null) {
            $xml->$elementName = $value;

            return $this;
        }

        if ($elementName == '') {
            $xml[$elementIndex] = $value;

            return $this;
        }

        // Old versions of php did not allow adding attributes to
        // non-existing elements by implicitly creating it.
        // We have to explicitly create the element.
        if (is_string($elementIndex) && empty($xml->$elementName)) {
            $el = $xml->addChild($elementName);
        } else {
            $el = $xml->$elementName;
        }

        $el[$elementIndex] = $value;

        return $this;
    }

    public function get($path)
    {
        $entries = explode('/', $path);
        $lastEntry = array_pop($entries);
        reset($entries);

        // navigate to the right place in the xml - create entries ad hoc as needed
        $xml = $this->root;
        foreach ($entries as $pathEntry) {
            $xml = $this->getNextElement($xml, $pathEntry);
            if ($xml === null) {
                return null;
            }
        }

        list($elementName, $elementIndex) = $this->parsePathEntry($lastEntry);

        if ($elementIndex === '') {
            throw new LogicException('Bad operator []');
        }

        if ($elementIndex === null) {
            return isset($xml->$elementName) ? strval($xml->$elementName) : null;
        }

        if ($elementName == '') {
            return isset($xml[$elementIndex]) ? strval($xml[$elementIndex]) : null;
        }

        if (!isset($xml->$elementName)) {
            return null;
        }

        if (!isset($xml->$elementName[$elementIndex])) {
            return null;
        }

        return strval($xml->$elementName[$elementIndex]);
    }
}
