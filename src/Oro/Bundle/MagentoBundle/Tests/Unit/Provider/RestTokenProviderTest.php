<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider;

use FOS\RestBundle\Util\Codes;

use Doctrine\ORM\EntityManager;

use Psr\Log\NullLogger;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

use Oro\Bundle\SecurityBundle\Encoder\Mcrypt;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\IntegrationBundle\Test\FakeRestClient;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Oro\Bundle\IntegrationBundle\Test\FakeRestResponse;
use Oro\Bundle\MagentoBundle\Exception\InvalidConfigurationException;
use Oro\Bundle\MagentoBundle\Provider\RestTokenProvider;
use Oro\Bundle\MagentoBundle\Exception\RuntimeException;

class RestTokenProviderTest extends \PHPUnit_Framework_TestCase
{
    const TOKEN = 'token';
    const TOKEN_ENCRYPTED = 'token_encrypted';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | MagentoTransport
     */
    protected $transportEntity;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject | EntityManager
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

    /** @var \PHPUnit_Framework_MockObject_MockObject |  Mcrypt */
    protected $mcrypt;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $this->parameterBag = new ParameterBag();

        $this->transportEntity = $this->createMock(MagentoTransport::class);

        $this->transportEntity
            ->method('getSettingsBag')
            ->willReturn($this->parameterBag);

        $this->entityManager = $this->createMock(EntityManager::class);

        $doctrine = $this->createMock(RegistryInterface::class);
        $doctrine
            ->method('getEntityManagerForClass')
            ->with(Transport::class)
            ->willReturn($this->entityManager);

        $this->mcrypt = $this->createMock(Mcrypt::class);

        $this->tokenProvider = new RestTokenProvider($doctrine, $this->mcrypt);
        $this->tokenProvider->setLogger(new NullLogger());

        $this->client = new FakeRestClient();
    }

    protected function tearDown()
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
                'httpCode' => CODES::HTTP_UNAUTHORIZED,
                'expectedException' => InvalidConfigurationException::class,
            ],
            'Non-unauthorized exception' => [
                'code' => CODES::HTTP_BAD_REQUEST,
                'expectedException' => RuntimeException::class,
                'expectedExceptionMessage' => 'Server returned unexpected response. Response code 400'
            ]
        ];
    }

    public function testValidResultAndTransportEntityAlreadySavedToDB()
    {
        $this->client->setDefaultResponse(
            new FakeRestResponse(CODES::HTTP_OK, [], sprintf('"%s"', self::TOKEN))
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

        $this->mcrypt
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
            new FakeRestResponse(CODES::HTTP_OK, [], sprintf('"%s"', self::TOKEN))
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

        $this->mcrypt
            ->expects($this->atLeastOnce())
            ->method('encryptData')
            ->with(self::TOKEN)
            ->willReturn(self::TOKEN_ENCRYPTED);

        $this->setUpdateEntityNeverCall();

        $this->tokenProvider->generateNewToken($this->transportEntity, $this->client);
    }

    /**
     * @expectedException Oro\Bundle\MagentoBundle\Exception\RuntimeException
     * @expectedExceptionMessage Unable to parse response body into JSON
     */
    public function testResponseContainsBrokenJson()
    {
        $this->client->setDefaultResponse(
            new FakeRestResponse(CODES::HTTP_OK, [], '\token')
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

        $this->mcrypt
            ->expects($this->atLeastOnce())
            ->method('decryptData')
            ->with(self::TOKEN_ENCRYPTED)
            ->willReturn(self::TOKEN);

        $this->assertEquals(
            self::TOKEN,
            $this->tokenProvider->getTokenFromEntity($this->transportEntity)
        );
    }

    protected function setUpdateEntityNeverCall()
    {
        $this->entityManager->expects($this->never())->method('persist')->with($this->transportEntity);
        $this->entityManager->expects($this->never())->method('flush')->with($this->transportEntity);
    }
}
