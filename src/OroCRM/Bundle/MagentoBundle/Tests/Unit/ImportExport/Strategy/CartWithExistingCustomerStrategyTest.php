<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Importexport\Strategy;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\CartWithExistingCustomerStrategy;

class CartWithExistingCustomerStrategyTest extends AbstractExistingCustomerStrategyTest
{
    /**
     * @return CartWithExistingCustomerStrategy
     */
    protected function getStrategy()
    {
        return new CartWithExistingCustomerStrategy(
            $this->strategyHelper,
            $this->managerRegistry,
            $this->ownerHelper
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
        $cart = new Cart();
        $cart->setCustomer($customer);
        $cart->setChannel($channel);

        $repository = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['originId' => $customer->getOriginId(), 'channel' => $channel]);
        $this->em->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($repository));

        $this->assertNull($this->getStrategy()->process($cart));
    }

    public function testProcess()
    {
        $customer = new Customer();
        $customer->setOriginId(1);
        $channel = new Channel();
        $cart = new Cart();
        $cart->setCustomer($customer);
        $cart->setChannel($channel);

        $strategy = $this->getStrategy();

        $execution = $this->getMock('Akeneo\Bundle\BatchBundle\Item\ExecutionContext');
        $this->jobExecution->expects($this->any())->method('getExecutionContext')
            ->will($this->returnValue($execution));
        $strategy->setStepExecution($this->stepExecution);

        $repository = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with(['originId' => $customer->getOriginId(), 'channel' => $channel]);
        $this->em->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($repository));

        $cartItem = ['customerId' => uniqid()];
        /** @var \PHPUnit_Framework_MockObject_MockObject|ContextInterface $context */
        $context = $this->getMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');
        $context->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($cartItem));
        $strategy->setImportExportContext($context);

        $execution->expects($this->once())
            ->method('get')
            ->with(CartWithExistingCustomerStrategy::CONTEXT_CART_POST_PROCESS);
        $execution->expects($this->once())
            ->method('put')
            ->with(CartWithExistingCustomerStrategy::CONTEXT_CART_POST_PROCESS, [$cartItem]);

        $this->assertNull($strategy->process($cart));
    }
}
