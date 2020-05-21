<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\EventListener;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\ChannelBundle\Tests\Unit\EventListener\UpdateIntegrationConnectorsListenerTest as BaseTestCase;
use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use Oro\Bundle\MagentoBundle\EventListener\UpdateIntegrationConnectorsListener;
use Oro\Bundle\MagentoBundle\Provider\MagentoChannelType;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;
use Oro\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class UpdateIntegrationConnectorsListenerTest extends BaseTestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|TypesRegistry */
    protected $typesRegistry;

    /** @var \PHPUnit\Framework\MockObject\MockObject|MagentoTransportInterface */
    protected $transport;

    protected function setUp(): void
    {
        parent::setUp();

        $this->typesRegistry = $this->createMock('Oro\Bundle\IntegrationBundle\Manager\TypesRegistry');
        $this->transport = $this->createMock(MagentoTransportInterface::class);

        $this->transport
             ->expects($this->any())
             ->method('isSupportedExtensionVersion')
             ->willReturn(false);

        $this->typesRegistry
             ->expects($this->any())
             ->method('getTransportTypeBySettingEntity')
             ->willReturn($this->transport);
    }

    /**
     * @return UpdateIntegrationConnectorsListener
     */
    protected function getListener()
    {
        $listener = new UpdateIntegrationConnectorsListener($this->settingProvider, $this->registry);
        $listener->setConnectorsTypeRegistry($this->typesRegistry);

        return $listener;
    }

    /**
     * @param bool $isExtensionInstalled
     * @param array $expectedConnectors
     *
     * @dataProvider extensionDataProvider
     */
    public function testOnChannelSaveWithExtension($isExtensionInstalled, array $expectedConnectors)
    {
        $this->entity->setChannelType(MagentoChannelType::TYPE);
        $transport = new MagentoSoapTransport();
        $transport->setIsExtensionInstalled($isExtensionInstalled);
        $transport->setExtensionVersion(SoapTransport::REQUIRED_EXTENSION_VERSION);
        $this->integration->setTransport($transport);

        $this->transport
            ->expects($this->any())
            ->method('isExtensionInstalled')
            ->willReturn($isExtensionInstalled);

        $orderConnector = $this->getMockBuilder('Oro\Bundle\MagentoBundle\Provider\Connector\OrderConnector')
            ->disableOriginalConstructor()
            ->getMock();

        $cartConnector = $this->getMockBuilder('Oro\Bundle\MagentoBundle\Provider\Connector\CartConnector')
            ->disableOriginalConstructor()
            ->getMock();

        $dictionaryConnector = $this
            ->createMock('Oro\Bundle\MagentoBundle\Provider\Connector\DictionaryConnectorInterface');

        $this->typesRegistry->expects($this->any())
            ->method('getConnectorType')
            ->will(
                $this->returnValueMap(
                    [
                        [
                            'magento',
                            'TestConnector1',
                            $orderConnector
                        ],
                        [
                            'magento',
                            'TestConnector2',
                            $cartConnector
                        ]
                    ]
                )
            );

        $this->typesRegistry->expects($this->any())
            ->method('getRegisteredConnectorsTypes')
            ->willReturn(new ArrayCollection(['dictionaryConnector' => $dictionaryConnector]));

        $this->prepareEvent();
        $this->getListener()->onChannelSave($this->event);

        $this->assertEquals($expectedConnectors, $this->integration->getConnectors());
    }

    /**
     * @param bool $isExtensionInstalled
     * @param string $version
     * @param array $expectedConnectors
     *
     * @dataProvider installedExtensionDataProvider
     */
    public function testCartConnectorNotRelyOnVersion($isExtensionInstalled, $version, array $expectedConnectors)
    {
        $this->entity->setChannelType(MagentoChannelType::TYPE);
        $transport = new MagentoSoapTransport();
        $transport->setIsExtensionInstalled($isExtensionInstalled);
        $transport->setExtensionVersion($version);
        $this->integration->setTransport($transport);

        $this->transport
            ->expects($this->any())
            ->method('isExtensionInstalled')
            ->willReturn($isExtensionInstalled);

        $extensionAwareConnector = $this
            ->getMockBuilder('Oro\Bundle\MagentoBundle\Provider\ExtensionAwareInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $versionAwareConnector = $this
            ->getMockBuilder('Oro\Bundle\MagentoBundle\Provider\ExtensionVersionAwareInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->typesRegistry->expects($this->any())
            ->method('getConnectorType')
            ->will(
                $this->returnValueMap(
                    [
                        [
                            'magento',
                            'TestConnector1',
                            $versionAwareConnector
                        ],
                        [
                            'magento',
                            'TestConnector2',
                            $extensionAwareConnector
                        ]
                    ]
                )
            );

        $this->typesRegistry->expects($this->any())
            ->method('getRegisteredConnectorsTypes')
            ->willReturn(new ArrayCollection());

        $this->prepareEvent();
        $this->getListener()->onChannelSave($this->event);

        $this->assertEquals($expectedConnectors, $this->integration->getConnectors());
    }

    /**
     * @return array
     */
    public function installedExtensionDataProvider()
    {
        return [
            [
                false,
                SoapTransport::REQUIRED_EXTENSION_VERSION,
                []
            ],
            [
                true,
                0,
                [
                    'TestConnector2_initial',
                    'TestConnector2'
                ]
            ]
        ];
    }

    public function testOnChannelSave()
    {
        $this->entity->setChannelType(MagentoChannelType::TYPE);
        $transport = new MagentoSoapTransport();
        $transport->setIsExtensionInstalled(false);
        $this->integration->setTransport($transport);

        $this->typesRegistry->expects($this->any())
            ->method('getRegisteredConnectorsTypes')
            ->willReturn(new ArrayCollection([]));

        $this->prepareEvent();
        $this->getListener()->onChannelSave($this->event);
    }

    /**
     * @return array
     */
    public function extensionDataProvider()
    {
        return [
            [false, ['dictionaryConnector', 'TestConnector1_initial', 'TestConnector1']],
            [true,
                [
                    'dictionaryConnector',
                    'TestConnector1_initial',
                    'TestConnector2_initial',
                    'TestConnector1',
                    'TestConnector2'
                ]
            ]
        ];
    }

    public function testNonMagentoChannel()
    {
        $this->event->expects($this->once())
            ->method('getChannel')
            ->will($this->returnValue($this->entity));
        $this->event->expects($this->never())
            ->method('getDataSource');

        $this->em->expects($this->never())
            ->method($this->anything());

        $this->getListener()->onChannelSave($this->event);
    }
}
