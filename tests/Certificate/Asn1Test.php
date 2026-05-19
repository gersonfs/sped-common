<?php

namespace NFePHP\Common\Tests\Certificate;

use NFePHP\Common\Certificate\Asn1;

class Asn1Test extends \PHPUnit\Framework\TestCase
{
    const TEST_PUBLIC_KEY = '/../fixtures/certs/x99999090910270_pubKEY.pem';

    public function testGetCNPJ()
    {
        $expected = '99999090910270';
        $actual = Asn1::getCNPJ(file_get_contents(__DIR__ . self::TEST_PUBLIC_KEY));
        $this->assertEquals($expected, $actual);
    }

    public function testGetCNPJReturnsNullWhenOidNotPresent()
    {
        $key = file_get_contents(__DIR__ . '/../fixtures/certs/e-CPF_pubkey.pem');
        $this->assertNull(Asn1::getCNPJ($key));
    }

    public function testGetCPF()
    {
        $key = file_get_contents(__DIR__ . '/../fixtures/certs/e-CPF_pubkey.pem');
        $this->assertSame('80767940130', Asn1::getCPF($key));
    }

    public function testGetCPFReturnsNullWhenOidNotPresent()
    {
        $key = file_get_contents(__DIR__ . self::TEST_PUBLIC_KEY);
        $this->assertNull(Asn1::getCPF($key));
    }

    public function testGetOIDdataReturnsEmptyWhenNotFound()
    {
        // OID inválido garante que o marker não é encontrado
        $this->assertSame(
            '',
            Asn1::getOIDdata('1.2.3.4.5.6.7.8.9', file_get_contents(__DIR__ . self::TEST_PUBLIC_KEY))
        );
    }
}
