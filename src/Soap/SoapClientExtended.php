<?php

namespace NFePHP\Common\Soap;

use SoapClient;

class SoapClientExtended extends SoapClient
{
    /**
     * __construct
     * @param mixed $wsdl NULL for non-wsdl mode or URL string for wsdl mode
     * @param array $options
     */
    public function __construct($wsdl, $options)
    {
        parent::__construct($wsdl, $options);
    }

    /**
     * __doRequest
     * Changes the original behavior of the class by removing prefixes,
     * suffixes and line breaks that are not supported by some webservices
     * due to their particular settings.
     *
     * Os parâmetros 5 e 6 ficam SEM type hint para tolerar três variações
     * do SoapClient::__doRequest entre versões:
     *  - PHP 7.4: $one_way = NULL (sem tipo)
     *  - PHP < 8.5: bool $oneWay = false (5 parâmetros)
     *  - PHP 8.5+: bool $oneWay = false, ?string $uriParserClass = null (6 parâmetros)
     *
     * @param string $request
     * @param string $location
     * @param string $action
     * @param int $version
     * @param mixed $oneWay
     * @param mixed $uriParserClass
     * @return string|null
     */
    #[\ReturnTypeWillChange]
    public function __doRequest($request, $location, $action, $version, $oneWay = false, $uriParserClass = null)
    {
        $search = [":ns1","ns1:","\n","\r"];
        $cleanRequest = str_replace($search, '', $request);
        // O tipo do 5º parâmetro do SoapClient::__doRequest mudou entre versões
        // (int no PHP 7.4, bool no PHP 8.x) e o 6º parâmetro só existe a partir
        // do PHP 8.5. Resolvemos via reflexão dinâmica para evitar incompatibilidade
        // tanto em runtime quanto nos diferentes stubs do PHPStan.
        $args = [$cleanRequest, $location, $action, $version, $oneWay];
        if ($uriParserClass !== null) {
            $args[] = $uriParserClass;
        }
        $parentMethod = new \ReflectionMethod(\SoapClient::class, '__doRequest');
        return $parentMethod->invokeArgs($this, $args);
    }
}
