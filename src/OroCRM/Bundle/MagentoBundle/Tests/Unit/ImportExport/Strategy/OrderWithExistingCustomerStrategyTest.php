<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Importexport\Strategy;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\OrderWithExistingCustomerStrategy;

class OrderWithExistingCustomerStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $strategyHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $managerRegistry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $ownerHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $em;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var OrderWithExistingCustomerStrategy
     */
    protected $strategy;

    protected function setUp()
    {
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->strategyHelper = $this
            ->getMockBuilder('Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->strategyHelper->expects($this->any())
            ->method('getEntityManager')
            ->will($this->returnValue($this->em));
        $this->managerRegistry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $this->ownerHelper = $this
            ->getMockBuilder('Oro\Bundle\IntegrationBundle\ImportExport\Helper\DefaultOwnerHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->context = $this->getMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');

        $this->strategy = new OrderWithExistingCustomerStrategy(
            $this->strategyHelper,
            $this->managerRegistry,
            $this->ownerHelper
        );
        $this->strategy->setImportExportContext($this->context);
    }

    protected function tearDown()
    {
        unset(
            $this->em,
            $this->strategyHelper,
            $this->managerRegistry,
            $this->ownerHelper,
            $this->context,
            $this->strategy
        );
    }

    public function testProcess()
    {
        $customer = new Customer();
        $customer->setOriginId(1);
        $channel = new Channel();
        $order = new Order();
        $order->setCustomer($customer);
        $order->setChannel($channel);

        $repository = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['originId' => $customer->getOriginId(), 'channel' => $channel]);
        $this->em->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($repository));

        $this->context->expects($this->once())
            ->method('getValue')
            ->with(OrderWithExistingCustomerStrategy::CONTEXT_ORDER_POST_PROCESS);
        $this->context->expects($this->once())
            ->method('setValue')
            ->with(OrderWithExistingCustomerStrategy::CONTEXT_ORDER_POST_PROCESS, [$order]);

        $this->assertNull($this->strategy->process($order));
    }
}
