<?php

require 'src/Xml.php';

function ensure($condition)
{
    if (!$condition) {
        throw new RuntimeException('Condition failed');
    }
}

$xml = new Moccalotto\Exemel\Xml(new SimpleXmlElement('<root/>'));
// <root />

$xml->set('[attr]', 'attr0');
ensure($xml->get('[attr]') == 'attr0');
ensure($xml->root()['attr'] == 'attr0');
// <root attr="attr0" />

$xml->set('foo/bar', 'el1');
ensure($xml->get('foo/bar') == 'el1');
ensure((string) $xml->root()->foo->bar == 'el1');
// <root attr="attr0">
//   <foo>
//     <bar>el1</bar>
//   </foo>
// </root>

$xml->set('foo/bar[ding]', 'attr1');
ensure($xml->get('foo/bar[ding]') == 'attr1');
ensure((string) $xml->root()->foo->bar['ding'] == 'attr1');
// <root attr="attr0">
//   <foo>
//     <bar ding="attr1">el1</bar>
//   </foo>
// </root>

$xml->set('foo[]/bar', 'el2');
ensure($xml->get('foo[1]/bar') == 'el2');
ensure((string) $xml->root()->foo[1]->bar == 'el2');
// <root attr="attr0">
//   <foo>
//     <bar ding="attr1">el1</bar>
//   </foo>
//   <foo>
//     <bar>el2</bar>
//   </foo>
// </root>
