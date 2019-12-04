<?php

namespace Oro\Bundle\MagentoBundle\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Client\RestClientInterface;
use Oro\Bundle\IntegrationBundle\Provider\Rest\Exception\RestException;
use Oro\Bundle\MagentoBundle\Entity\MagentoRestTransport;
use Oro\Bundle\MagentoBundle\Exception\InvalidConfigurationException;
use Oro\Bundle\MagentoBundle\Exception\RuntimeException;
use Oro\Bundle\MagentoBundle\Utils\ValidationUtils;
use Oro\Bundle\SecurityBundle\Encoder\SymmetricCrypterInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;

/**
 * This class provides ability to get REST API tokens for magento integration
 */
class RestTokenProvider implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    const USER_KEY = 'api_user';
    const PASSWORD_KEY = 'api_key';

    const USER_API_PARAM = 'username';
    const PASSWORD_API_PARAM = 'password';

    const TOKEN_RETRIEVAL_URL = 'integration/admin/token';

    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @var SymmetricCrypterInterface
     */
    protected $crypter;

    /**
     * @param ManagerRegistry $doctrine
     * @param SymmetricCrypterInterface $crypter
     */
    public function __construct(ManagerRegistry $doctrine, SymmetricCrypterInterface $crypter)
    {
        $this->doctrine = $doctrine;
        $this->crypter  = $crypter;
    }

    /**
     * @param MagentoRestTransport $transportEntity
     * @param RestClientInterface $client
     *
     * @return string
     */
    public function getTokenFromEntity(MagentoRestTransport $transportEntity, RestClientInterface $client)
    {
        $encryptedToken = $transportEntity->getApiToken();
        if (null === $encryptedToken) {
            return $this->generateNewToken($transportEntity, $client);
        }

        return $this->crypter->decryptData($encryptedToken);
    }

    /**
     * @param MagentoRestTransport    $transportEntity
     * @param RestClientInterface $client
     *
     * @return string
     */
    public function generateNewToken(MagentoRestTransport $transportEntity, RestClientInterface $client)
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
     *
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
     *
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
        if (Response::HTTP_UNAUTHORIZED === $statusCode) {
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
     *
     * @throws InvalidConfigurationException
     */
    protected function getTokenRequestParams(ParameterBag $parameterBag)
    {
        $username = $parameterBag->get(static::USER_KEY);
        $encryptedPassword = $parameterBag->get(static::PASSWORD_KEY);

        if (null === $username || null === $encryptedPassword) {
            throw new InvalidConfigurationException(
                "Magento REST transport require 'api_key' and 'api_user' settings to be defined."
            );
        }

        $password = $this->crypter->decryptData($encryptedPassword);

        return [
            static::USER_API_PARAM     => $username,
            static::PASSWORD_API_PARAM => $password
        ];
    }

    /**
     * @param MagentoRestTransport $transportEntity
     * @param string    $token
     */
    protected function updateToken(MagentoRestTransport $transportEntity, $token)
    {
        $transportEntity->setApiToken(
            $this->crypter->encryptData($token)
        );

        /**
         * Save api-token only if entity already saved to the database
         */
        if ($transportEntity->getId()) {
            $em = $this->doctrine->getManagerForClass(Transport::class);
            $em->persist($transportEntity);
            $em->flush($transportEntity);
        }
    }
}
