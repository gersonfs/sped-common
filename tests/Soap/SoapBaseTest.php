<?php

namespace NFePHP\Common\Tests\Soap;

use NFePHP\Common\Certificate;
use NFePHP\Common\Files;
use NFePHP\Common\Soap\SoapBase;
use NFePHP\Common\Soap\SoapFake;
use NFePHP\Common\Soap\SoapInterface;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

class SoapBaseTest extends TestCase
{
    const TEST_PFX_FILE = '/../fixtures/certs/certificado_teste.pfx';

    /**
     * setAccessible(true) é obrigatório no PHP 7.4–8.0 para acesso a membros
     * não públicos via Reflection. A partir do 8.1 não tem efeito, e no 8.5 emite
     * E_DEPRECATED. Encapsulamos com supressão para manter compatibilidade.
     *
     * @param ReflectionMethod|ReflectionProperty $member
     */
    private function reflectionMakeAccessible($member): void
    {
        @$member->setAccessible(true);
    }

    private function newSoap(): SoapFake
    {
        return new SoapFake();
    }

    /**
     * uid() é usado para compor o caminho do diretório temporário ("/sped-{uid}/").
     * O contrato é devolver um valor convertível para string não vazia.
     */
    public function testUidReturnsStringableNonEmptyValue()
    {
        $soap = $this->newSoap();
        $method = new ReflectionMethod(SoapBase::class, 'uid');
        $this->reflectionMakeAccessible($method);
        $uid = $method->invoke($soap);
        $this->assertNotSame('', (string) $uid);
    }

    /**
     * randomName() deve devolver uma string com o formato esperado pelo cURL/SoapClient
     * (subdiretório certs/ + nome aleatório + .pem).
     */
    public function testRandomNameReturnsValidPemFileName()
    {
        $soap = $this->newSoap();
        $reflection = new ReflectionClass(SoapBase::class);

        $certsDir = $reflection->getProperty('certsdir');
        $this->reflectionMakeAccessible($certsDir);
        $certsDir->setValue($soap, 'certs/');

        $filesystem = $reflection->getProperty('filesystem');
        $this->reflectionMakeAccessible($filesystem);
        $filesystem->setValue($soap, new Files(sys_get_temp_dir() . '/sped-common-test-' . uniqid() . '/'));

        $method = $reflection->getMethod('randomName');
        $this->reflectionMakeAccessible($method);
        $name = $method->invoke($soap);
        $this->assertIsString($name);
        $this->assertStringStartsWith('certs/', $name);
        $this->assertStringEndsWith('.pem', $name);
    }

    /**
     * removeTemporarilyFiles() não pode lançar erro quando ainda não há
     * filesystem ou certsdir configurados (estado inicial do objeto).
     */
    public function testRemoveTemporarilyFilesDoesNothingOnFreshInstance()
    {
        $soap = $this->newSoap();
        $soap->removeTemporarilyFiles();
        $this->assertInstanceOf(SoapBase::class, $soap);
    }

    /**
     * Ciclo completo de gravação/remoção de arquivos temporários: exercita
     * saveTemporarilyKeyFiles() -> removeTemporarilyFiles() (que internamente
     * chama listContents() do filesystem local).
     */
    public function testSaveAndRemoveTemporarilyKeyFiles()
    {
        $certificate = Certificate::readPfx(
            file_get_contents(__DIR__ . self::TEST_PFX_FILE),
            'associacao'
        );
        $soap = new SoapFake();
        $soap->disableCertValidation(true);
        $soap->loadCertificate($certificate);
        $folder = sys_get_temp_dir() . '/sped-common-test-' . uniqid() . '/';
        $soap->setTemporaryFolder($folder);

        $soap->saveTemporarilyKeyFiles();

        $reflection = new ReflectionClass(SoapBase::class);
        $certsdir = $reflection->getProperty('certsdir');
        $this->reflectionMakeAccessible($certsdir);
        $this->assertSame('certs/', $certsdir->getValue($soap));

        $prifile = $reflection->getProperty('prifile');
        $this->reflectionMakeAccessible($prifile);
        $priPath = $folder . $prifile->getValue($soap);
        $this->assertFileExists($priPath);

        $soap->removeTemporarilyFiles();
        $this->assertFileDoesNotExist($priPath);
    }

    /**
     * O parâmetro $soapheader em send() deve ser compatível entre interface,
     * classe abstrata e implementações concretas (default null, tipo opcional
     * SoapHeader). Garante essa compatibilidade via reflexão.
     */
    public function testSendSignatureCompatibility()
    {
        foreach ([SoapInterface::class, SoapBase::class, SoapFake::class] as $class) {
            $params = (new ReflectionMethod($class, 'send'))->getParameters();
            $this->assertSame('soapheader', $params[7]->getName(), "Em $class");
            $this->assertTrue($params[7]->isOptional(), "Em $class soapheader deve ser opcional");
            $this->assertNull($params[7]->getDefaultValue(), "Em $class default deve ser null");
        }
    }
}
