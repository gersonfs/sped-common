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
     * Mantém apenas os 4 parâmetros estáveis em toda a história do
     * SoapClient e usa um variadic para acomodar tudo a partir do 5º
     * (que mudou de int para bool entre PHP 7.4 e 8.5, e ganhou
     * $uriParserClass no PHP 8.5). Isso evita conflito de variância
     * entre versões.
     *
     * @param string $request
     * @param string $location
     * @param string $action
     * @param int $version
     * @param mixed ...$rest
     * @return string|null
     */
    #[\ReturnTypeWillChange]
    public function __doRequest($request, $location, $action, $version, ...$rest)
    {
        $search = [":ns1","ns1:","\n","\r"];
        return parent::__doRequest(
            str_replace($search, '', $request),
            $location,
            $action,
            $version,
            ...$rest
        );
    }
}
