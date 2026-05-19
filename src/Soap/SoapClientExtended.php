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
        // O 6º parâmetro só existe a partir do PHP 8.5. Em versões anteriores
        // passar um argumento extra à parent quebra; por isso decidimos aqui.
        if ($uriParserClass === null) {
            return parent::__doRequest($cleanRequest, $location, $action, $version, (bool) $oneWay);
        }
        /** @phpstan-ignore-next-line arguments.count (parent ganhou um 6º arg só no PHP 8.5; stub do PHPStan ainda não cobre) */
        return parent::__doRequest($cleanRequest, $location, $action, $version, (bool) $oneWay, $uriParserClass);
    }
}
