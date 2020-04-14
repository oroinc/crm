<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;
use Oro\Bundle\MagentoBundle\Provider\CartExpirationProcessor;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoSoapTransportInterface;
use Oro\Bundle\MagentoBundle\Provider\Transport\SoapTransport;
use Symfony\Component\HttpFoundation\ParameterBag;

class CartExpirationProcessorTest extends \PHPUnit\Framework\TestCase
{
    const BATCH_SIZE = 2;

    /** @var CartExpirationProcessor */
    protected $processor;

    /** @var EntityManager|\PHPUnit\Framework\MockObject\MockObject */
    protected $em;

    /** @var ConnectorContextMediator|\PHPUnit\Framework\MockObject\MockObject */
    protected $helper;

    protected function setUp(): void
    {
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()->getMock();

        $this->helper = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator')
            ->disableOriginalConstructor()->getMock();

        $this->processor = new CartExpirationProcessor($this->helper, $this->em, self::BATCH_SIZE);
    }

    protected function tearDown(): void
    {
        unset($this->em, $this->helper, $this->processor);
    }

    public function testProcessConfigurationExceptionScenario()
    {
        $this->expectException(\Oro\Bundle\MagentoBundle\Exception\ExtensionRequiredException::class);
        $settingBag = new ParameterBag();

        $transport = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Transport')
            ->setMethods(['getSettingsBag'])->getMockForAbstractClass();
        $transport->expects($this->any())->method('getSettingsBag')
            ->will($this->returnValue($settingBag));

        $realTransport = $this->createMock(MagentoSoapTransportInterface::class);
        $realTransport->expects($this->once())->method('isSupportedExtensionVersion')->will($this->returnValue(false));

        $this->helper->expects($this->once())->method('getTransport')
            ->will($this->returnValue($realTransport));

        $channel = new Channel();
        $channel->setTransport($transport);

        $this->processor->process($channel);
    }

    public function testProcessDataExceptionScenario()
    {
        $this->expectException(\LogicException::class);
        $testWebsiteId   = 1;
        $testStoresArray = new \ArrayIterator([]);
        $settingBag      = new ParameterBag(['website_id' => $testWebsiteId]);

        $transport = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Transport')
            ->setMethods(['getSettingsBag'])->getMockForAbstractClass();
        $transport->expects($this->any())->method('getSettingsBag')
            ->will($this->returnValue($settingBag));

        $realTransport = $this->createMock(MagentoSoapTransportInterface::class);
        $realTransport->expects($this->once())->method('isSupportedExtensionVersion')
            ->will($this->returnValue(true));
        $realTransport->expects($this->once())->method('getStores')
            ->will($this->returnValue($testStoresArray));

        $this->helper->expects($this->once())->method('getTransport')
            ->will($this->returnValue($realTransport));

        $channel = new Channel();
        $channel->setTransport($transport);

        $this->processor->process($channel);
    }

    public function testProcessGoodScenario()
    {
        $testWebsiteId   = 1;
        $testStoreId     = 2;
        $testStoresArray = new \ArrayIterator([['website_id' => $testWebsiteId, 'store_id' => $testStoreId]]);
        $settingBag      = new ParameterBag(['website_id' => $testWebsiteId]);
        $testData        = [
            ['id' => 1, 'originId' => 11],
            ['id' => 2, 'originId' => 22],
            ['id' => 3, 'originId' => 33],
        ];

        $testExistedCarts = [
            (object)['entity_id' => 22]
        ];

        $repo = $this->getMockBuilder('Oro\Bundle\MagentoBundle\Entity\Repository\CartRepository')
            ->disableOriginalConstructor()->getMock();
        $this->em->expects($this->any())->method('getRepository')->with('OroMagentoBundle:Cart')
            ->will($this->returnValue($repo));

        $transport = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Transport')
            ->setMethods(['getSettingsBag'])->getMockForAbstractClass();
        $transport->expects($this->any())->method('getSettingsBag')
            ->will($this->returnValue($settingBag));

        $realTransport = $this->createMock(MagentoSoapTransportInterface::class);
        $realTransport->expects($this->once())->method('isSupportedExtensionVersion')
            ->will($this->returnValue(true));
        $realTransport->expects($this->once())->method('getStores')
            ->will($this->returnValue($testStoresArray));

        $this->helper->expects($this->once())->method('getTransport')
            ->will($this->returnValue($realTransport));

        $channel = new Channel();
        $channel->setTransport($transport);

        $realTransport->expects($this->at(3))->method('call')
            ->with(
                SoapTransport::ACTION_ORO_CART_LIST,
                [
                    'filters' => [
                        'complex_filter' => [
                            ['key' => 'store_id', 'value' => ['key' => 'in', 'value' => $testStoreId]],
                            ['key' => 'entity_id', 'value' => ['key' => 'in', 'value' => '11,22']]
                        ]
                    ],
                    'pager'   => ['page' => 1, 'pageSize' => self::BATCH_SIZE]
                ]
            )->will($this->returnValue($testExistedCarts));
        $realTransport->expects($this->at(4))->method('call')
            ->with(
                SoapTransport::ACTION_ORO_CART_LIST,
                [
                    'filters' => [
                        'complex_filter' => [
                            ['key' => 'store_id', 'value' => ['key' => 'in', 'value' => $testStoreId]],
                            ['key' => 'entity_id', 'value' => ['key' => 'in', 'value' => '33']]
                        ]
                    ],
                    'pager'   => ['page' => 1, 'pageSize' => self::BATCH_SIZE]
                ]
            )->will($this->returnValue([]));

        $repo->expects($this->once())->method('getCartsByChannelIdsIterator')->with($channel)
            ->will($this->returnValue($testData));

        $repo->expects($this->at(1))->method('markExpired')->with([1]);
        $repo->expects($this->at(2))->method('markExpired')->with([3]);

        $this->processor->process($channel);
    }
}
