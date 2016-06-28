<?php

namespace spec\Moccalotto\Exemel;

use Moccalotto\Exemel\Xml;
use SimpleXmlElement;
use Prophecy\Argument;
use PhpSpec\ObjectBehavior;

class XmlSpec extends ObjectBehavior
{
    public function getMatchers()
    {
        return [
            'beXmlSimilar' => function ($selfXml, $otherXml) {
                $selfParser = xml_parser_create();
                xml_parse_into_struct($selfParser, $selfXml, $selfArray, $selfIndex);
                xml_parser_free($selfParser);

                $otherParser = xml_parser_create();
                xml_parse_into_struct($otherParser, $otherXml, $otherArray, $otherIndex);
                xml_parser_free($otherParser);

                return $selfArray == $otherArray && $selfIndex == $otherIndex;
            },
        ];
    }

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
        $expected = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root/>
XML;
        $this->beConstructedWith(new SimpleXmlElement('<root></root>'));

        $this->root()->asXml()->shouldBeXmlSimilar($expected);
        $this->formatted()->shouldBeXmlSimilar($expected);
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
        $this->formatted()->shouldBeXmlSimilar($expected);
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
        $this->formatted()->shouldBeXmlSimilar($expected);
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
        $this->set('foo/bar/[ding]', 'dong');
        $this->get('foo/bar/[ding]')->shouldBe('dong');
        $this->root()->foo->bar->attributes()->count()->shouldBe(1);
        $this->formatted()->shouldBeXmlSimilar($expected);
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
        $this->set('foo/bar/[ding]', 'will-be-overwritten');

        $this->set('foo/bar', 'el1');
        $this->set('foo/bar/[ding]', 'dong');
        $this->set('foo[]/bar', 'el2');
        $this->formatted()->shouldBeXmlSimilar($expected);
    }


    public function it_can_read_element_values()
    {
        $this->beConstructedWith(
            new SimpleXmlElement('<root><foo>FOO</foo><bar>BAR</bar><baz><sub>SUB</sub></baz></root>')
        );
        $this->get('foo')->shouldBe('FOO');
        $this->get('bar')->shouldBe('BAR');
        $this->get('baz')->shouldBe('');
        $this->get('baz/sub')->shouldBe('SUB');
        $this->get('fling')->shouldBe(null);
    }

    public function it_can_make_complex_reads()
    {
        $this->beConstructedWith(new SimpleXmlElement('<root></root>'));

        $this->set('foo/bar', 'will-be-overwritten');
        $this->set('foo/bar/[ding]', 'will-be-overwritten');

        $this->set('foo/bar', 'el1');
        $this->set('foo/bar[ding]', 'dong');
        $this->set('foo[]/bar', 'el2');
        $this->set('foo[]/bar', 'el3');
        $this->set('foo[3]/bar[ding]', 'dong');
        $this->set('foo[3]/bar[1]/[ding]', 'dong');

        $this->get('foo/bar')->shouldBe('el1');
        $this->get('foo[1]/bar')->shouldBe('el2');
        $this->get('foo[2]/bar')->shouldBe('el3');
        $this->get('foo[3]/bar[ding]')->shouldBe('dong');
        $this->get('foo[3]/bar[1]/[ding]')->shouldBe('dong');
    }

    public function it_can_detect_similar_xml()
    {
        $self = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
  <foo>
    <bar a="a" b="b">el1</bar>
  </foo>
  <baz c="c" d="d" />
</root>

XML;
        $matchesAll = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
  <foo>
    <bar b="b" a="a">el1</bar>
  </foo>
  <baz d="d" c="c"></baz>
</root>

XML;

        $matchesCase = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>


  <foo>

    <bar b="b" a="a">el1</bar>

  </foo>

  <baz d="d" c="c"></baz>

</root>

XML;

        $matchesWhitespace = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<ROOT>
  <FOO>
    <BAR a="a" b="b">el1</BAR>
  </FOO>
  <BAZ c="c" d="d" />
</ROOT>

XML;

        $this->beConstructedWith(new SimpleXmlElement($self));

        $this->sameAs($matchesAll, true, true)->shouldBe(true);
        $this->sameAs($matchesAll, true, false)->shouldBe(true);
        $this->sameAs($matchesAll, false, true)->shouldBe(true);
        $this->sameAs($matchesAll, false, false)->shouldBe(true);

        $this->sameAs($matchesCase, true, true)->shouldBe(true);
        $this->sameAs($matchesCase, true, false)->shouldBe(true);
        $this->sameAs($matchesCase, false, true)->shouldBe(false);
        $this->sameAs($matchesCase, false, false)->shouldBe(false);

        $this->sameAs($matchesWhitespace, true, true)->shouldBe(true);
        $this->sameAs($matchesWhitespace, true, false)->shouldBe(false);
        $this->sameAs($matchesWhitespace, false, true)->shouldBe(true);
        $this->sameAs($matchesWhitespace, false, false)->shouldBe(false);
    }
}
