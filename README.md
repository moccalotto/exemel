# Exemel
[![Build Status](https://travis-ci.org/moccalotto/exemel.svg)](https://travis-ci.org/moccalotto/exemel)

Easily construct and modify XML documents using fluint API.

## Installation

To add this package as a local, per-project dependency to your project, simply add a dependency on
 `moccalotto/exemel` to your project's `composer.json` file like so:

```json
{
    "require": {
        "moccalotto/exemel": "~0.1"
    }
}
```

Alternatively simply call `composer require moccalotto/exemel`


## Demo

```php
<?php

require 'vendor/autoload.php';

function ensure($condition)
{
    if (!$condition) {
        throw new RuntimeException('Condition failed');
    }
}

$xml = new Moccalotto\Exemel\Xml(new SimpleXmlElement('<root/>'));
// <root />


/*
 * Add attr="attr0" to the root element
 */
$xml->set('[attr]', 'attr0');
ensure($xml->get('[attr]') == 'attr0');
ensure($xml->root()['attr'] == 'attr0');
// <root attr="attr0" />



/*
 * Add an element called <foo>
 * Inside that, add an element called <bar>
 * Set the contents of <bar> to "el1"
 */
$xml->set('foo/bar', 'el1');
ensure($xml->get('foo/bar') == 'el1');
ensure((string) $xml->root()->foo->bar == 'el1');
// <root attr="attr0">
//   <foo>
//     <bar>el1</bar>
//   </foo>
// </root>



/*
 * Add attr="attr1" to the newly added <bar> element
 */
$xml->set('foo/bar[ding]', 'attr1');
ensure($xml->get('foo/bar[ding]') == 'attr1');
ensure((string) $xml->root()->foo->bar['ding'] == 'attr1');
// <root attr="attr0">
//   <foo>
//     <bar ding="attr1">el1</bar>
//   </foo>
// </root>



/*
 * Add a new <foo> element to the root.
 * Inside that, add a <bar> element with the contents "el2"
 */
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

echo $xml->indentedXml();
```
