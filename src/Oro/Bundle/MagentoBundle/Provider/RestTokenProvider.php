<?php

namespace Oro\Bundle\MagentoBundle\Provider;

use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestClientInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestResponseInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;

class RestTokenProvider
{
    const USER_KEY = 'api_key';
    const PASSWORD_KEY = 'api_user';

    const USER_API_PARAM = 'username';
    const PASSWORD_API_PARAM = 'password';

    const TOKEN_RETRIEVAL_URL = 'integration/admin/token';

    /**
     * @param ParameterBag        $parameterBag
     * @param RestClientInterface $client
     *
     * @return array
     */
    public function getToken(ParameterBag $parameterBag, RestClientInterface $client)
    {
        $tokenRequestParams = $this->getTokenRequestParams($parameterBag);
        $token = $this->doTokenRequest($client, $tokenRequestParams);

        /**
         * @todo Save token to database
         */

        return $token;
    }

    /**
     * @param RestClientInterface $client
     * @param array               $params
     *
     * @return string
     */
    protected function doTokenRequest(RestClientInterface $client, array $params)
    {
        try {
            $response = $client->get(static::TOKEN_RETRIEVAL_URL, $params);
            $this->validateStatusCodes($response->getStatusCode());
            return $response->getBodyAsString();
        } catch (RestException $exception) {
            /**
             * @todo throw custom exception
             */
        }
    }

    /**
     * @param RestResponseInterface $response
     *
     * @throws \Exception
     */
    protected function validateStatusCodes(RestResponseInterface $response)
    {
        $statusCode = $response->getStatusCode();
        if (200 === $statusCode) {
            return;
        }

        if (401 === $statusCode) {
            throw new InvalidConfigurationException(
                'Magento REST transport require \'api_key\' and \'api_user\' settings to be defined.'
            );
        }

        /**
         * @todo throw custom exception
         */
        throw new \Exception('Server does\'t response !');
    }

    /**
     * @param ParameterBag $parameterBag
     *
     * @return array
     */
    protected function getTokenRequestParams(ParameterBag $parameterBag)
    {
        $username = $parameterBag->get(static::USER_KEY, false);
        $password = $parameterBag->get(static::PASSWORD_KEY, false);
        /**
         * @todo Implement checking of MagentoRestTransportInterface
         */
        if (!$username || !$password) {
            throw new InvalidConfigurationException(
                'Magento REST transport require \'api_key\' and \'api_user\' settings to be defined.'
            );
        }

        return [
            static::USER_API_PARAM => $username,
            static::PASSWORD_API_PARAM => $password
        ];
    }
}
