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
     * Usa variadic ($extra) para acomodar parâmetros adicionais que versões
     * mais novas do PHP introduzem em SoapClient::__doRequest (ex.: o
     * $uriParserClass surgido no PHP 8.5).
     *
     * @param string $request
     * @param string $location
     * @param string $action
     * @param int $version
     * @param bool $oneWay
     * @param mixed ...$extra
     * @return string|null
     */
    #[\ReturnTypeWillChange]
    public function __doRequest($request, $location, $action, $version, $oneWay = false, ...$extra)
    {
        $search = [":ns1","ns1:","\n","\r"];
        return parent::__doRequest(
            str_replace($search, '', $request),
            $location,
            $action,
            $version,
            (bool) $oneWay,
            ...$extra
        );
    }
}
