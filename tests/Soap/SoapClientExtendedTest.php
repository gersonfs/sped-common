<?php

namespace NFePHP\Common\Tests\Soap;

use NFePHP\Common\Soap\SoapClientExtended;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use SoapClient;

class SoapClientExtendedTest extends TestCase
{
    public function testExtendsSoapClient()
    {
        $ref = new \ReflectionClass(SoapClientExtended::class);
        $this->assertSame(SoapClient::class, $ref->getParentClass()->getName());
    }

    /**
     * Para sobreviver às mudanças do SoapClient::__doRequest entre versões
     * (int -> bool no PHP 8.5, novo parâmetro $uriParserClass também no 8.5),
     * a classe filha mantém apenas os 4 primeiros parâmetros e captura o
     * restante via variadic.
     */
    public function testDoRequestSignatureIsCompatible()
    {
        $params = (new ReflectionMethod(SoapClientExtended::class, '__doRequest'))->getParameters();
        $this->assertCount(5, $params);
        $this->assertSame('request', $params[0]->getName());
        $this->assertSame('version', $params[3]->getName());
        $this->assertTrue($params[4]->isVariadic());
    }
}
