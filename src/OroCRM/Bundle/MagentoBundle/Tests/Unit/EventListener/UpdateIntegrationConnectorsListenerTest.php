<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\EventListener;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use OroCRM\Bundle\ChannelBundle\Tests\Unit\EventListener\UpdateIntegrationConnectorsListenerTest as BaseTestCase;
use OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use OroCRM\Bundle\MagentoBundle\EventListener\UpdateIntegrationConnectorsListener;
use OroCRM\Bundle\MagentoBundle\Provider\ChannelType;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class UpdateIntegrationConnectorsListenerTest extends BaseTestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|TypesRegistry */
    protected $typesRegistry;

    protected function setUp()
    {
        parent::setUp();

        $this->typesRegistry = $this->getMock('Oro\Bundle\IntegrationBundle\Manager\TypesRegistry');
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
        $this->entity->setChannelType(ChannelType::TYPE);
        $transport = new MagentoSoapTransport();
        $transport->setIsExtensionInstalled($isExtensionInstalled);
        $transport->setExtensionVersion(SoapTransport::REQUIRED_EXTENSION_VERSION);
        $this->integration->setTransport($transport);

        $orderConnector = $this->getMockBuilder('OroCRM\Bundle\MagentoBundle\Provider\OrderConnector')
            ->disableOriginalConstructor()
            ->getMock();

        $cartConnector = $this->getMockBuilder('OroCRM\Bundle\MagentoBundle\Provider\CartConnector')
            ->disableOriginalConstructor()
            ->getMock();

        $dictionaryConnector = $this
            ->getMock('OroCRM\Bundle\MagentoBundle\Provider\Connector\DictionaryConnectorInterface');

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
        $this->entity->setChannelType(ChannelType::TYPE);
        $transport = new MagentoSoapTransport();
        $transport->setIsExtensionInstalled($isExtensionInstalled);
        $transport->setExtensionVersion($version);
        $this->integration->setTransport($transport);

        $extensionAwareConnector = $this
            ->getMockBuilder('OroCRM\Bundle\MagentoBundle\Provider\ExtensionAwareInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $versionAwareConnector = $this
            ->getMockBuilder('OroCRM\Bundle\MagentoBundle\Provider\ExtensionVersionAwareInterface')
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
        $this->entity->setChannelType(ChannelType::TYPE);
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
