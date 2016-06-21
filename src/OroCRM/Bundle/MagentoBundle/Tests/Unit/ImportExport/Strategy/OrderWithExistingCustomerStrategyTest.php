<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\ImportExport\Strategy;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\OrderWithExistingCustomerStrategy;

class OrderWithExistingCustomerStrategyTest extends AbstractStrategyTest
{
    /**
     * {@inheritdoc}
     */
    protected function getStrategy()
    {
        return new OrderWithExistingCustomerStrategy(
            $this->eventDispatcher,
            $this->strategyHelper,
            $this->fieldHelper,
            $this->databaseHelper,
            $this->chainEntityClassNameProvider,
            $this->translator,
            $this->newEntitiesHelper
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Execution context is not configured
     */
    public function testProcessFailed()
    {
        $customer = new Customer();
        $customer->setOriginId(1);
        $channel = new Channel();
        $order = new Order();
        $cart = new Cart();
        $order->setCustomer($customer);
        $order->setChannel($channel);
        $order->setCart($cart);

        $strategy = $this->getStrategy();
        /** @var \PHPUnit_Framework_MockObject_MockObject|ContextInterface $context */
        $context = $this->getMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');
        $strategy->setImportExportContext($context);
        $this->assertNull($strategy->process($order));
    }

    public function testProcess()
    {
        $customer = new Customer();
        $customer->setOriginId(1);
        $transport = $this->getMockBuilder('OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport')
            ->disableOriginalConstructor()
            ->getMock();
        $transport->expects($this->once())
            ->method('getIsExtensionInstalled')
            ->will($this->returnValue(true));
        $channel = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Channel')
            ->disableOriginalConstructor()
            ->getMock();
        $channel->expects($this->once())
            ->method('getTransport')
            ->will($this->returnValue($transport));
        $order = new Order();
        $cart = new Cart();
        $cart->setOriginId(1);
        $order->setCustomer($customer);
        $order->setChannel($channel);
        $order->setCart($cart);

        $this->databaseHelper->expects($this->once())
            ->method('findOneByIdentity')
            ->with($channel)
            ->will($this->returnValue($channel));

        $strategy = $this->getStrategy();

        $execution = $this->getMock('Akeneo\Bundle\BatchBundle\Item\ExecutionContext');
        $this->jobExecution->expects($this->any())->method('getExecutionContext')
            ->will($this->returnValue($execution));
        $strategy->setStepExecution($this->stepExecution);

        $orderItemDate = ['customerId' => uniqid()];
        /** @var \PHPUnit_Framework_MockObject_MockObject|ContextInterface $context */
        $context = $this->getMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');
        $context->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($orderItemDate));
        $strategy->setImportExportContext($context);

        $execution->expects($this->exactly(3))
            ->method('get')
            ->with($this->isType('string'));
        $execution->expects($this->exactly(3))
            ->method('put')
            ->with($this->isType('string'), $this->isType('array'));

        $this->assertNull($strategy->process($order));
    }
}
