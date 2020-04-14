<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Test\FakeRestClient;
use Oro\Bundle\IntegrationBundle\Test\FakeRestResponse;
use Oro\Bundle\MagentoBundle\Entity\MagentoRestTransport;
use Oro\Bundle\MagentoBundle\Exception\InvalidConfigurationException;
use Oro\Bundle\MagentoBundle\Exception\RuntimeException;
use Oro\Bundle\MagentoBundle\Provider\RestTokenProvider;
use Oro\Bundle\SecurityBundle\Encoder\SymmetricCrypterInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Response;

class RestTokenProviderTest extends \PHPUnit\Framework\TestCase
{
    const TOKEN = 'token';
    const TOKEN_ENCRYPTED = 'token_encrypted';

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | MagentoRestTransport
     */
    protected $transportEntity;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject | EntityManager
     */
    protected $entityManager;

    /**
     * @var  RestTokenProvider
     */
    protected $tokenProvider;

    /**
     * @var ParameterBag
     */
    protected $parameterBag;

    /**
     * @var FakeRestClient
     */
    protected $client;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject |  SymmetricCrypterInterface
     */
    protected $crypter;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->parameterBag = new ParameterBag();

        $this->transportEntity = $this->createMock(MagentoRestTransport::class);

        $this->transportEntity
            ->method('getSettingsBag')
            ->willReturn($this->parameterBag);

        $this->entityManager = $this->createMock(EntityManager::class);

        $doctrine = $this->createMock(ManagerRegistry::class);
        $doctrine
            ->method('getManagerForClass')
            ->with(Transport::class)
            ->willReturn($this->entityManager);

        $this->crypter = $this->createMock(SymmetricCrypterInterface::class);

        $this->tokenProvider = new RestTokenProvider($doctrine, $this->crypter);
        $this->tokenProvider->setLogger(new NullLogger());

        $this->client = new FakeRestClient();
    }

    protected function tearDown(): void
    {
        unset(
            $this->transportEntity,
            $this->entityManager,
            $this->tokenProvider,
            $this->parameterBag,
            $this->client
        );
    }

    public function testInvalidTokenRequestParams()
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->setUpdateEntityNeverCall();
        $this->tokenProvider->generateNewToken($this->transportEntity, $this->client);
    }

    /**
     * @dataProvider getExceptionProvider
     *
     * @param string $httpCode
     * @param string $expectedException
     * @param string $expectedExceptionMessage
     */
    public function testClientExceptions($httpCode, $expectedException, $expectedExceptionMessage = null)
    {
        $this->client->setDefaultResponse(
            new FakeRestResponse($httpCode)
        );

        $this->parameterBag->add([
             RestTokenProvider::USER_KEY => 'api_user',
             RestTokenProvider::PASSWORD_KEY => 'api_key'
        ]);

        $this->expectException($expectedException);
        if (isset($expectedExceptionMessage)) {
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        $this->transportEntity
            ->expects($this->never())
            ->method('setApiToken')
            ->with('token');

        $this->setUpdateEntityNeverCall();
        $this->tokenProvider->generateNewToken($this->transportEntity, $this->client);
    }

    public function getExceptionProvider()
    {
        return [
            'Unauthorized exception' => [
                'httpCode' => Response::HTTP_UNAUTHORIZED,
                'expectedException' => InvalidConfigurationException::class,
            ],
            'Non-unauthorized exception' => [
                'code' => Response::HTTP_BAD_REQUEST,
                'expectedException' => RuntimeException::class,
                'expectedExceptionMessage' => 'Server returned unexpected response. Response code 400'
            ]
        ];
    }

    public function testValidResultAndTransportEntityAlreadySavedToDB()
    {
        $this->client->setDefaultResponse(
            new FakeRestResponse(Response::HTTP_OK, [], sprintf('"%s"', self::TOKEN))
        );
        $this->parameterBag->add([
            RestTokenProvider::USER_KEY => 'api_user',
            RestTokenProvider::PASSWORD_KEY => 'api_key_encrypted'
        ]);

        $this->transportEntity
            ->expects($this->atLeastOnce())
            ->method('setApiToken')
            ->with(self::TOKEN_ENCRYPTED);

        $this->transportEntity
            ->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(1);

        $this->crypter
            ->expects($this->atLeastOnce())
            ->method('encryptData')
            ->with(self::TOKEN)
            ->willReturn(self::TOKEN_ENCRYPTED);

        $this->entityManager->expects($this->once())->method('persist')->with($this->transportEntity);
        $this->entityManager->expects($this->once())->method('flush')->with($this->transportEntity);

        $this->tokenProvider->generateNewToken($this->transportEntity, $this->client);
    }

    public function testValidResultAndTransportEntityNotSavedToDB()
    {
        $this->client->setDefaultResponse(
            new FakeRestResponse(Response::HTTP_OK, [], sprintf('"%s"', self::TOKEN))
        );
        $this->parameterBag->add([
            RestTokenProvider::USER_KEY => 'api_user',
            RestTokenProvider::PASSWORD_KEY => 'api_key'
        ]);

        $this->transportEntity
            ->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(null);

        $this->transportEntity
            ->expects($this->atLeastOnce())
            ->method('setApiToken')
            ->with(self::TOKEN_ENCRYPTED);

        $this->crypter
            ->expects($this->atLeastOnce())
            ->method('encryptData')
            ->with(self::TOKEN)
            ->willReturn(self::TOKEN_ENCRYPTED);

        $this->setUpdateEntityNeverCall();

        $this->tokenProvider->generateNewToken($this->transportEntity, $this->client);
    }

    public function testResponseContainsBrokenJson()
    {
        $this->expectException(\Oro\Bundle\MagentoBundle\Exception\RuntimeException::class);
        $this->expectExceptionMessage('Unable to parse response body into JSON');

        $this->client->setDefaultResponse(
            new FakeRestResponse(Response::HTTP_OK, [], '\token')
        );
        $this->parameterBag->add([
            RestTokenProvider::USER_KEY => 'api_user',
            RestTokenProvider::PASSWORD_KEY => 'api_key'
        ]);
        $this->transportEntity
            ->expects($this->never())
            ->method('setApiToken')
            ->with('token');

        $this->setUpdateEntityNeverCall();
        $this->tokenProvider->generateNewToken($this->transportEntity, $this->client);
    }

    public function testGetTokenFromEntity()
    {
        $this->transportEntity
            ->expects($this->atLeastOnce())
            ->method('getApiToken')
            ->willReturn(self::TOKEN_ENCRYPTED);

        $this->crypter
            ->expects($this->atLeastOnce())
            ->method('decryptData')
            ->with(self::TOKEN_ENCRYPTED)
            ->willReturn(self::TOKEN);

        $this->assertEquals(
            self::TOKEN,
            $this->tokenProvider->getTokenFromEntity($this->transportEntity, $this->client)
        );
    }

    protected function setUpdateEntityNeverCall()
    {
        $this->entityManager->expects($this->never())->method('persist')->with($this->transportEntity);
        $this->entityManager->expects($this->never())->method('flush')->with($this->transportEntity);
    }
}
