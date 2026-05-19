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
     * O parâmetro $oneWay precisa ser bool para combinar com a assinatura
     * de SoapClient::__doRequest (que mudou para bool em versões recentes do PHP)
     * e evitar Fatal error de incompatibilidade de assinatura. Um variadic
     * final acomoda parâmetros adicionais introduzidos pelo PHP 8.5+.
     */
    public function testDoRequestSignatureIsCompatible()
    {
        $params = (new ReflectionMethod(SoapClientExtended::class, '__doRequest'))->getParameters();
        $this->assertCount(6, $params);
        $this->assertSame('oneWay', $params[4]->getName());
        $this->assertTrue($params[4]->isOptional());
        $this->assertSame(false, $params[4]->getDefaultValue());
        $this->assertTrue($params[5]->isVariadic());
    }
}
