<?php

namespace NFePHP\Common\Tests\Tags;

use NFePHP\Common\Tags\MakeBase;
use NFePHP\Common\Tags\Tag;
use NFePHP\Common\Tags\TagInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * Stub mínimo de Tag para verificar o comportamento de MakeBase.
 */
class FakeStdTag extends Tag implements TagInterface
{
    /**
     * @var \stdClass
     */
    public $std;

    public function __construct($dom = null)
    {
        parent::__construct($dom);
        $this->std = new stdClass();
    }

    public function loadParameters(\stdClass $std)
    {
        $this->std = $std;
    }

    public function toNode()
    {
        return $this->dom->createElement('fake');
    }
}

class FakeMake extends MakeBase
{
    protected $rootname = 'NFe';
    protected $xmlns = 'http://www.portalfiscal.inf.br/nfe';

    protected $available = [
        'tagide' => ['class' => FakeStdTag::class, 'type' => 'single'],
        'tagemit' => ['class' => FakeStdTag::class, 'type' => 'single'],
        'taginfnfe' => ['class' => FakeStdTag::class, 'type' => 'single'],
    ];

    public function parse()
    {
    }
}

class MakeBaseTest extends TestCase
{
    public function testCallStoresSingleProperty()
    {
        $make = new FakeMake();
        $std = new stdClass();
        $std->cuf = '35';
        $result = $make->tagide($std);
        $this->assertInstanceOf(FakeStdTag::class, $result);
        $this->assertInstanceOf(FakeStdTag::class, $make->ide);
        $this->assertSame('35', $make->ide->std->cuf);
    }

    public function testCallThrowsWhenMethodNotAvailable()
    {
        $make = new FakeMake();
        $std = new stdClass();
        $this->expectException(\Exception::class);
        $this->expectExceptionMessageMatches('/Não encontrada referencia ao método/');
        $make->tagunknown($std);
    }

    public function testSetToAsciiPropagatesToStdArgument()
    {
        $make = new FakeMake();
        $make->setToAscii(true);
        $std = new stdClass();
        $std->cuf = '35';
        $make->tagide($std);
        // __call grava onlyAscii no próprio std passado
        $this->assertTrue($std->onlyAscii);
    }

    public function testCreatePropertyAssignsTag()
    {
        $make = new FakeMake();
        $tag = new FakeStdTag();
        $make->createProperty('foo', $tag);
        $this->assertSame($tag, $make->foo);
    }

    public function testSetToAscii()
    {
        $make = new FakeMake();
        $this->assertFalse($make->setToAscii());
        $this->assertTrue($make->setToAscii(true));
        $this->assertFalse($make->setToAscii(false));
    }
}
