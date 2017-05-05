<?php

namespace Oro\Bundle\MagentoBundle\Provider\Transport;

use Symfony\Component\HttpFoundation\ParameterBag;

use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\BridgeRestClientFactory;

class RestClientFactory extends BridgeRestClientFactory
{
    const API_URL_PREFIX = 'rest/V1';

    /**
     * @inheritDoc
     */
    protected function getClientBaseUrl(ParameterBag $parameterBag)
    {
        return rtrim($parameterBag->get('api_url'), '/') . '/' . ltrim(static::API_URL_PREFIX, '/');
    }

    /**
     * @inheritDoc
     */
    protected function getClientOptions(ParameterBag $parameterBag)
    {
        return [
            'headers' => [
                'Accept' => 'application/json'
            ]
        ];
    }
}
