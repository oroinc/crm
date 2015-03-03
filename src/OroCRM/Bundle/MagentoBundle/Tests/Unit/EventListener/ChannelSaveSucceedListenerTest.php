<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\EventListener;

use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use OroCRM\Bundle\ChannelBundle\Tests\Unit\EventListener\ChannelSaveSucceedListenerTest as BaseTestCase;
use OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use OroCRM\Bundle\MagentoBundle\EventListener\ChannelSaveSucceedListener;
use OroCRM\Bundle\MagentoBundle\Provider\ChannelType;

class ChannelSaveSucceedListenerTest extends BaseTestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|TypesRegistry */
    protected $typesRegistry;

    protected function setUp()
    {
        parent::setUp();

        $this->typesRegistry = $this->getMock('Oro\Bundle\IntegrationBundle\Manager\TypesRegistry');
    }

    /**
     * @return ChannelSaveSucceedListener
     */
    protected function getListener()
    {
        $listener = new ChannelSaveSucceedListener($this->settingProvider, $this->registry);
        $listener->setConnectorsTypeRegistry($this->typesRegistry);

        return $listener;
    }

    public function assertConnectorsEmptyIfTransportEmptyAndTypeNotMatched()
    {
        $this->assertEmpty($this->integration->getConnectors());
    }

    public function assertConnectorsEmptyIfTransportEmpty()
    {
        $this->entity->setChannelType(ChannelType::TYPE);

        $this->event->expects($this->once())
            ->method('getChannel')
            ->will($this->returnValue($this->entity));
        $this->getListener()->onChannelSucceedSave($this->event);

        $this->assertEmpty($this->integration->getConnectors());
    }

    public function assertSuccessWithNotMatchedConnectors()
    {
        $this->entity->setChannelType(ChannelType::TYPE);
        $this->integration->setTransport(new MagentoSoapTransport());

        $this->event->expects($this->once())
            ->method('getChannel')
            ->will($this->returnValue($this->entity));
        $this->getListener()->onChannelSucceedSave($this->event);

        $this->assertEmpty($this->integration->getConnectors());
    }

    /**
     * @param bool $isExtensionInstalled
     * @param array $expectedConnectors
     *
     * @dataProvider extensionDataProvider
     */
    public function testOnChannelSucceedSaveWithExtension($isExtensionInstalled, array $expectedConnectors)
    {
        $this->entity->setChannelType(ChannelType::TYPE);
        $transport = new MagentoSoapTransport();
        $transport->setIsExtensionInstalled($isExtensionInstalled);
        $this->integration->setTransport($transport);


        $orderConnector = $this->getMockBuilder('OroCRM\Bundle\MagentoBundle\Provider\OrderConnector')
            ->disableOriginalConstructor()
            ->getMock();

        $cartConnector = $this->getMockBuilder('OroCRM\Bundle\MagentoBundle\Provider\CartConnector')
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

        parent::testOnChannelSucceedSave();

        $this->assertEquals($expectedConnectors, $this->integration->getConnectors());
    }

    /**
     * @return array
     */
    public function extensionDataProvider()
    {
        return [
            [false, ['TestConnector1_initial', 'TestConnector1']],
            [true, ['TestConnector1_initial', 'TestConnector2_initial', 'TestConnector1', 'TestConnector2']]
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

        $this->getListener()->onChannelSucceedSave($this->event);
    }
}
