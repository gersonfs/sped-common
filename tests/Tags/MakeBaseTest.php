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
        'tagdet' => ['class' => FakeStdTag::class, 'type' => 'multiple'],
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

    /**
     * Tag do tipo "multiple" deve acumular instâncias em um array — esse caminho
     * dependia da correção do createProperty($name, []) que violava o type hint.
     */
    public function testCallAccumulatesMultiplePropertyAsArray()
    {
        $make = new FakeMake();
        $std1 = new stdClass();
        $std1->n = '1';
        $std2 = new stdClass();
        $std2->n = '2';
        $make->tagdet($std1);
        $result = $make->tagdet($std2);

        $this->assertIsArray($result);
        $this->assertIsArray($make->det);
        $this->assertCount(2, $make->det);
        $this->assertSame('1', $make->det[0]->std->n);
        $this->assertSame('2', $make->det[1]->std->n);
    }

    /**
     * checkIdKey() reconstrói a chave de 44 dígitos, sincroniza infnfe->std->id
     * e atualiza ide->std->cdv com o DV — testa a correção do typo $infId/$infid.
     */
    public function testCheckIdKeyRebuildsKeyAndSyncsInfnfe()
    {
        $make = new FakeMake();

        $emit = new FakeStdTag();
        $emit->std = (object) ['cnpj' => '99999090910270'];

        $ide = new FakeStdTag();
        $ide->std = (object) [
            'cuf' => '35',
            'dhemi' => '2020-01-15T10:00:00-03:00',
            'mod' => '55',
            'serie' => '1',
            'nnf' => '123',
            'tpemis' => '1',
            'cnf' => '12345678',
            'cdv' => null,
        ];

        $infnfe = new FakeStdTag();
        $infnfe->std = (object) ['id' => 'NFe00000000000000000000000000000000000000000'];

        $make->emit = $emit;
        $make->ide = $ide;
        $make->infnfe = $infnfe;

        $ref = new \ReflectionClass(MakeBase::class);
        $method = $ref->getMethod('checkIdKey');
        @$method->setAccessible(true);
        $built = $method->invoke($make);

        $this->assertIsString($built);
        $this->assertSame(44, strlen($built));
        $this->assertSame("NFe{$built}", $make->infnfe->std->id);
        $this->assertSame(substr($built, -1), $make->ide->std->cdv);
    }
}
