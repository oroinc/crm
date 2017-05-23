<?php

namespace Oro\Bundle\MagentoBundle\Provider;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

use FOS\RestBundle\Util\Codes;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerAwareInterface;

use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestClientInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\Exception\RuntimeException;
use Oro\Bundle\MagentoBundle\Exception\InvalidConfigurationException;
use Oro\Bundle\MagentoBundle\Utils\ValidationUtils;

class RestTokenProvider implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    const USER_KEY = 'api_user';
    const PASSWORD_KEY = 'api_key';

    const USER_API_PARAM = 'username';
    const PASSWORD_API_PARAM = 'password';

    const TOKEN_RETRIEVAL_URL = 'integration/admin/token';

    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    /**
     * @param RegistryInterface $doctrine
     */
    public function __construct(RegistryInterface $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param MagentoTransport    $transportEntity
     * @param RestClientInterface $client
     *
     * @return string
     */
    public function getToken(MagentoTransport $transportEntity, RestClientInterface $client)
    {
        $this->logger->info('Do request to get new `token`');

        $settings = $transportEntity->getSettingsBag();
        $tokenRequestParams = $this->getTokenRequestParams($settings);
        $token = $this->doTokenRequest($client, $tokenRequestParams);

        $this->logger->info('Request on new `token` is done');

        $this->updateToken($transportEntity, $token);

        return $token;
    }

    /**
     * @param RestClientInterface $client
     * @param array               $params
     *
     * @return string
     * @throws RestException
     */
    protected function doTokenRequest(RestClientInterface $client, array $params)
    {
        try {
            $response = $client->post(static::TOKEN_RETRIEVAL_URL, $params);
            return $response->json();
        } catch (RestException $e) {
            $this->validateStatusCodes($e);
        }
    }

    /**
     * @param RestException $e
     * @throws InvalidConfigurationException | RuntimeException
     */
    protected function validateStatusCodes(RestException $e)
    {
        $response = $e->getResponse();
        /**
         * Exception caused by incorrect client settings or invalid response body
         */
        if (null === $response) {
            throw new RuntimeException(
                ValidationUtils::sanitizeSecureInfo($e->getMessage()),
                $e->getCode(),
                $e
            );
        }

        $statusCode = $response->getStatusCode();
        if (Codes::HTTP_UNAUTHORIZED === $statusCode) {
            throw new InvalidConfigurationException(
                "Can't get token by defined 'api_key' and 'api_user'. Please check credentials !"
            );
        }

        throw new RuntimeException(sprintf('Server returned unexpected response. Response code %s', $statusCode));
    }

    /**
     * @param ParameterBag $parameterBag
     *
     * @return array
     * @throws InvalidConfigurationException
     */
    protected function getTokenRequestParams(ParameterBag $parameterBag)
    {
        $username = $parameterBag->get(static::USER_KEY, false);
        $password = $parameterBag->get(static::PASSWORD_KEY, false);

        if (!$username || !$password) {
            throw new InvalidConfigurationException(
                "Magento REST transport require 'api_key' and 'api_user' settings to be defined."
            );
        }

        return [
            static::USER_API_PARAM => $username,
            static::PASSWORD_API_PARAM => $password
        ];
    }

    /**
     * @param MagentoTransport $transportEntity
     * @param string    $token
     */
    protected function updateToken(MagentoTransport $transportEntity, $token)
    {
        $em = $this->doctrine->getEntityManagerForClass(Transport::class);
        $transportEntity->setApiToken($token);
        $em->persist($transportEntity);
        $em->flush($transportEntity);
    }
}
