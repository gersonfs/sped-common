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
     * ($one_way sem tipo no PHP 7.4 -> bool em 8.0–8.4 -> bool + ?string
     * $uriParserClass no 8.5), a classe filha aceita os 6 parâmetros
     * sem tipos explícitos, e decide em runtime se repassa ou não o 6º.
     */
    public function testDoRequestSignatureIsCompatible()
    {
        $params = (new ReflectionMethod(SoapClientExtended::class, '__doRequest'))->getParameters();
        $this->assertCount(6, $params);
        $this->assertSame('request', $params[0]->getName());
        $this->assertSame('version', $params[3]->getName());
        $this->assertSame('oneWay', $params[4]->getName());
        $this->assertSame('uriParserClass', $params[5]->getName());
        $this->assertTrue($params[4]->isOptional());
        $this->assertTrue($params[5]->isOptional());
    }
}
