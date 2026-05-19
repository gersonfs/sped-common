<?php

namespace NFePHP\Common\Tests\Exception;

use NFePHP\Common\Exception\CertificateException;
use NFePHP\Common\Exception\ExceptionInterface;
use NFePHP\Common\Exception\SignerException;
use NFePHP\Common\Exception\SoapException;
use NFePHP\Common\Exception\ValidatorException;
use PHPUnit\Framework\TestCase;

/**
 * Cobre as fábricas estáticas das exceções que usam "new static()". O objetivo é
 * garantir que cada método continua devolvendo uma instância concreta da própria
 * exceção (preservando o contrato ao migrar de `new static` para configuração
 * @phpstan-consistent-constructor).
 */
class ExceptionFactoriesTest extends TestCase
{
    public function testCertificateExceptionFactories()
    {
        foreach (
            [
                CertificateException::unableToRead(),
                CertificateException::unableToOpen(),
                CertificateException::signContent(),
                CertificateException::getPrivateKey(),
                CertificateException::signatureFailed(),
            ] as $exception
        ) {
            $this->assertInstanceOf(CertificateException::class, $exception);
            $this->assertInstanceOf(ExceptionInterface::class, $exception);
            $this->assertNotSame('', $exception->getMessage());
        }
    }

    public function testSignerExceptionFactories()
    {
        $cases = [
            SignerException::isNotXml(),
            SignerException::digestComparisonFailed(),
            SignerException::signatureComparisonFailed(),
            SignerException::tagNotFound('infNFe'),
        ];
        foreach ($cases as $exception) {
            $this->assertInstanceOf(SignerException::class, $exception);
            $this->assertInstanceOf(ExceptionInterface::class, $exception);
        }
        $this->assertStringContainsString('infNFe', $cases[3]->getMessage());
    }

    public function testSoapExceptionFactories()
    {
        $unableCurl = SoapException::unableToLoadCurl('detalhe');
        $this->assertInstanceOf(SoapException::class, $unableCurl);
        $this->assertStringContainsString('detalhe', $unableCurl->getMessage());

        $fault = SoapException::soapFault('falhou', 42);
        $this->assertInstanceOf(SoapException::class, $fault);
        $this->assertSame(42, $fault->getCode());
        $this->assertStringContainsString('falhou', $fault->getMessage());
    }

    public function testValidatorExceptionFactories()
    {
        $xmlErrors = ValidatorException::xmlErrors(['erro 1', 'erro 2']);
        $this->assertInstanceOf(ValidatorException::class, $xmlErrors);
        $this->assertStringContainsString('erro 1', $xmlErrors->getMessage());
        $this->assertStringContainsString('erro 2', $xmlErrors->getMessage());

        $notXml = ValidatorException::isNotXml();
        $this->assertInstanceOf(ValidatorException::class, $notXml);
    }
}
