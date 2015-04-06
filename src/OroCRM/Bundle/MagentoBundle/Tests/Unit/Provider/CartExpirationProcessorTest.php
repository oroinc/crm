<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider;

use Doctrine\ORM\EntityManager;

use Symfony\Component\HttpFoundation\ParameterBag;

use Oro\Bundle\IntegrationBundle\Entity\Channel;

use OroCRM\Bundle\MagentoBundle\Provider\CartExpirationProcessor;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;

class CartExpirationProcessorTest extends \PHPUnit_Framework_TestCase
{
    const BATCH_SIZE = 2;

    /** @var CartExpirationProcessor */
    protected $processor;

    /** @var EntityManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $em;

    /** @var ConnectorContextMediator|\PHPUnit_Framework_MockObject_MockObject */
    protected $helper;

    protected function setUp()
    {
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()->getMock();

        $this->helper = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator')
            ->disableOriginalConstructor()->getMock();

        $this->processor = new CartExpirationProcessor($this->helper, $this->em, self::BATCH_SIZE);
    }

    protected function tearDown()
    {
        unset($this->em, $this->helper, $this->processor);
    }

    /**
     * @expectedException \OroCRM\Bundle\MagentoBundle\Exception\ExtensionRequiredException
     */
    public function testProcessConfigurationExceptionScenario()
    {
        $settingBag = new ParameterBag();

        $transport = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Transport')
            ->setMethods(['getSettingsBag'])->getMockForAbstractClass();
        $transport->expects($this->any())->method('getSettingsBag')
            ->will($this->returnValue($settingBag));

        $realTransport = $this->getMock('OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface');
        $realTransport->expects($this->once())->method('isSupportedExtensionVersion')->will($this->returnValue(false));

        $this->helper->expects($this->once())->method('getTransport')
            ->will($this->returnValue($realTransport));

        $channel = new Channel();
        $channel->setTransport($transport);

        $this->processor->process($channel);
    }

    /**
     * @expectedException \LogicException
     */
    public function testProcessDataExceptionScenario()
    {
        $testWebsiteId   = 1;
        $testStoresArray = new \ArrayIterator([]);
        $settingBag      = new ParameterBag(['website_id' => $testWebsiteId]);

        $transport = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Transport')
            ->setMethods(['getSettingsBag'])->getMockForAbstractClass();
        $transport->expects($this->any())->method('getSettingsBag')
            ->will($this->returnValue($settingBag));

        $realTransport = $this->getMock('OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface');
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

        $repo = $this->getMockBuilder('OroCRM\Bundle\MagentoBundle\Entity\Repository\CartRepository')
            ->disableOriginalConstructor()->getMock();
        $this->em->expects($this->any())->method('getRepository')->with('OroCRMMagentoBundle:Cart')
            ->will($this->returnValue($repo));

        $transport = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Transport')
            ->setMethods(['getSettingsBag'])->getMockForAbstractClass();
        $transport->expects($this->any())->method('getSettingsBag')
            ->will($this->returnValue($settingBag));

        $realTransport = $this->getMock('OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface');
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
