<?php

namespace spec\Moccalotto\Exemel;

use Moccalotto\Exemel\Xml;
use SimpleXmlElement;
use Prophecy\Argument;
use PhpSpec\ObjectBehavior;

class XmlSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith(new SimpleXmlElement('<root></root>'));
        $this->shouldHaveType(Xml::class);
    }

    function it_gives_access_to_root_element()
    {
        $this->beConstructedWith(new SimpleXmlElement('<root></root>'));
        $this->root()->shouldHaveType(SimpleXmlElement::class);
    }

    function it_can_format_xml()
    {
        $this->beConstructedWith(new SimpleXmlElement('<root></root>'));
        $doc = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root/>

XML;
        $this->formatted()->shouldBe($doc);
    }

    function it_can_manipulate_attributes_on_root()
    {
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root attr="attr0"/>

XML;
        $this->beConstructedWith(new SimpleXmlElement('<root></root>'));
        $this->set('[attr]', 'attr0')->shouldHaveType(Xml::class);
        $this->get('[attr]')->shouldBe('attr0');
        $this->root()->attributes()->count()->shouldBe(1);
        $this->formatted()->shouldBe($expected);
    }

    function it_can_add_elements_to_root()
    {
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
  <foo>
    <bar>el1</bar>
  </foo>
</root>

XML;
        $this->beConstructedWith(new SimpleXmlElement('<root></root>'));
        $this->set('foo/bar', 'el1');
        $this->get('foo/bar')->shouldBe('el1');
        $this->root()->foo->bar->__toString()->shouldBe('el1');
        $this->formatted()->shouldBe($expected);
    }

    function it_can_manipulate_attributes_on_nested_elements()
    {
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
  <foo>
    <bar ding="dong"/>
  </foo>
</root>

XML;
        $this->beConstructedWith(new SimpleXmlElement('<root></root>'));
        $this->set('foo/bar[ding]', 'dong');
        $this->get('foo/bar[ding]')->shouldBe('dong');
        $this->root()->foo->bar->attributes()->count()->shouldBe(1);
        $this->formatted()->shouldBe($expected);
    }


    function it_can_add_many_elements_with_same_name()
    {
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
  <foo>
    <bar ding="dong">el1</bar>
  </foo>
  <foo>
    <bar>el2</bar>
  </foo>
</root>

XML;
        $this->beConstructedWith(new SimpleXmlElement('<root></root>'));

        $this->set('foo/bar', 'will-be-overwritten');
        $this->set('foo/bar[ding]', 'will-be-overwritten');

        $this->set('foo/bar', 'el1');
        $this->set('foo/bar[ding]', 'dong');
        $this->set('foo[]/bar', 'el2');
        $this->formatted()->shouldBe($expected);
    }
}
