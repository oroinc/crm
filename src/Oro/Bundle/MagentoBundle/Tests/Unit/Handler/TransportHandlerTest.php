<?php

namespace Oro\Bundle\MagentoBundle\Test\Unit\Handler;

use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use Oro\Bundle\MagentoBundle\Handler\TransportHandler;
use Oro\Bundle\MagentoBundle\Provider\ConnectorChoicesProvider;
use Oro\Bundle\MagentoBundle\Provider\TransportEntityProvider;
use Oro\Bundle\MagentoBundle\Provider\WebsiteChoicesProvider;
use Oro\Bundle\MagentoBundle\Tests\Unit\Stub\MagentoTransportProviderStub;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class TransportHandlerTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var TransportHandler */
    protected $transportHandler;

    /** @var  TypesRegistry|\PHPUnit\Framework\MockObject\MockObject */
    protected $typesRegistry;

    /** @var  TransportEntityProvider|\PHPUnit\Framework\MockObject\MockObject */
    protected $transportEntityProvider;

    /** @var  WebsiteChoicesProvider|\PHPUnit\Framework\MockObject\MockObject */
    protected $websiteChoicesProvider;

    /** @var ConnectorChoicesProvider|\PHPUnit\Framework\MockObject\MockObject  */
    protected $connectorChoicesProvider;

    /** @var  Request|\PHPUnit\Framework\MockObject\MockObject */
    protected $request;

    /** @var  MagentoTransportProviderStub */
    protected $magentoTransport;

    /** @var  MagentoSoapTransport */
    protected $transportEntity;

    protected function setUp(): void
    {
        $this->typesRegistry            = $this->createMock(TypesRegistry::class);
        $this->transportEntityProvider  = $this->createMock(TransportEntityProvider::class);
        $this->websiteChoicesProvider   = $this->createMock(WebsiteChoicesProvider::class);
        $this->connectorChoicesProvider = $this->createMock(ConnectorChoicesProvider::class);
        $this->magentoTransport         = new MagentoTransportProviderStub();
        $this->request                  = $this->createMock(Request::class);

        $requestStack = new RequestStack();
        $requestStack->push($this->request);

        $this->transportEntity          = $this->getEntity(MagentoSoapTransport::class);

        $this->transportHandler = new TransportHandler(
            $this->typesRegistry,
            $this->transportEntityProvider,
            $this->websiteChoicesProvider,
            $this->connectorChoicesProvider,
            $requestStack
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset(
            $this->typesRegistry,
            $this->transportEntityProvider,
            $this->websiteChoicesProvider,
            $this->connectorChoicesProvider,
            $this->magentoTransport,
            $this->request,
            $this->transportHandler
        );
    }

    /**
     * @dataProvider testGetCheckResponseProvider
     *
     * @param array $magentoTransportData
     * @param array $allowedConnectorsChoices
     * @param array $websiteChoices
     * @param array $expectedResponseData
     */
    public function testGetCheckResponse(
        array $magentoTransportData,
        array $allowedConnectorsChoices,
        array $websiteChoices,
        array $expectedResponseData
    ) {
        $this->request
            ->expects($this->atLeastOnce())
            ->method('get')
            ->will(
                $this->returnCallback(
                    function ($argument) {
                        switch ($argument) {
                            case TransportHandler::TRANSPORT_TYPE:
                                return 'magento_soap';
                            case TransportHandler::INTEGRATION_TYPE:
                                return 'magento';
                            case TransportEntityProvider::ENTITY_ID:
                                return 1;
                            default:
                                return null;
                        }
                    }
                )
            );

        $this->connectorChoicesProvider
             ->expects($this->once())
             ->method('getAllowedConnectorsChoices')
             ->willReturn($allowedConnectorsChoices);

        $this->websiteChoicesProvider
             ->expects($this->once())
             ->method('formatWebsiteChoices')
             ->willReturn($websiteChoices);

        $this->transportEntityProvider
            ->expects($this->once())
            ->method('getTransportEntityByRequest')
            ->willReturn($this->transportEntity);

        $this->typesRegistry
            ->expects($this->once())
            ->method('getTransportType')
            ->willReturn($this->magentoTransport);

        $this->magentoTransport->setData($magentoTransportData);

        $responseData = $this->transportHandler->getCheckResponse();

        $this->assertEquals($expectedResponseData, $responseData);
    }

    /**
     * @return array
     */
    public function testGetCheckResponseProvider()
    {
        return [
            "Check response with oro bridge extension version that doesn't support order notes" => [
                'magentoTransportData' => [
                    'extensionVersion' => '1.0.1',
                    'magentoVersion' => '1.9.3.1',
                    'requiredExtensionVersion' => '1.0.1',
                    'isSupportedExtensionVersion' => true,
                    'isExtensionInstalled' => true,
                    'adminUrl' => '/admin'
                ],
                'allowedConnectorsChoices' => [
                    'test' => 'test'
                ],
                'websiteChoices' => [
                    [
                        'id' => -1,
                        'label' => null,
                    ],
                ],
                'expectedResponseData' => [
                    'success' => true,
                    'websites' => [
                        [
                            'id' => -1,
                            'label' => null
                        ]
                    ],
                    'isExtensionInstalled' => true,
                    'magentoVersion' => '1.9.3.1',
                    'extensionVersion' => '1.0.1',
                    'requiredExtensionVersion' => '1.0.1',
                    'isSupportedVersion' => true,
                    'isOrderNoteSupportExtensionVersion' => false,
                    'connectors' => [
                        'test' => 'test'
                    ],
                    'adminUrl' => '/admin',
                ]
            ],
            "Check response with oro bridge extension version that supports order notes" => [
                'magentoTransportData' => [
                    'extensionVersion' => '1.2.19',
                    'magentoVersion' => '1.9.3.1',
                    'requiredExtensionVersion' => '1.0.1',
                    'isSupportedExtensionVersion' => true,
                    'isExtensionInstalled' => true,
                    'adminUrl' => '/admin'
                ],
                'allowedConnectorsChoices' => [
                    'test' => 'test'
                ],
                'websiteChoices' => [
                    [
                        'id' => -1,
                        'label' => null,
                    ],
                ],
                'expectedResponseData' => [
                    'success' => true,
                    'websites' => [
                        [
                            'id' => -1,
                            'label' => null
                        ]
                    ],
                    'isExtensionInstalled' => true,
                    'magentoVersion' => '1.9.3.1',
                    'extensionVersion' => '1.2.19',
                    'requiredExtensionVersion' => '1.0.1',
                    'isSupportedVersion' => true,
                    'isOrderNoteSupportExtensionVersion' => true,
                    'connectors' => [
                        'test' => 'test'
                    ],
                    'adminUrl' => '/admin',
                ]
            ]
        ];
    }
}
