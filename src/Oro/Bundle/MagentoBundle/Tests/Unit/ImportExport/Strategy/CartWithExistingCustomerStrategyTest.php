<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\ImportExport\Strategy;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\ImportExport\Strategy\CartWithExistingCustomerStrategy;

class CartWithExistingCustomerStrategyTest extends AbstractStrategyTest
{
    /**
     * {@inheritdoc}
     */
    protected function getStrategy()
    {
        return new CartWithExistingCustomerStrategy(
            $this->eventDispatcher,
            $this->strategyHelper,
            $this->fieldHelper,
            $this->databaseHelper,
            $this->chainEntityClassNameProvider,
            $this->translator,
            $this->newEntitiesHelper,
            $this->doctrineHelper,
            $this->relatedEntityStateHelper
        );
    }

    public function testProcessFailed()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Execution context is not configured');

        $strategy = $this->getStrategy();

        /** @var \PHPUnit\Framework\MockObject\MockObject|ContextInterface $context */
        $context = $this->createMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');
        $strategy->setImportExportContext($context);

        $customer = new Customer();
        $customer->setOriginId(1);
        $channel = new Channel();
        $cart = new Cart();
        $cart
            ->setCustomer($customer)
            ->setChannel($channel)
            ->setItemsCount(2)
            ->setEmail('email@example.com');

        $this->assertNull($strategy->process($cart));
    }

    public function testProcess()
    {
        $customer = new Customer();
        $customer->setOriginId(1);
        $channel = new Channel();
        $cart = new Cart();
        $cart
            ->setCustomer($customer)
            ->setChannel($channel)
            ->setItemsCount(2)
            ->setEmail('email@example.com');

        $strategy = $this->getStrategy();

        $execution = $this->createMock('Akeneo\Bundle\BatchBundle\Item\ExecutionContext');
        $this->jobExecution->expects($this->any())->method('getExecutionContext')
            ->will($this->returnValue($execution));
        $strategy->setStepExecution($this->stepExecution);

        $cartItem = ['customerId' => uniqid()];
        /** @var \PHPUnit\Framework\MockObject\MockObject|ContextInterface $context */
        $context = $this->createMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');
        $context->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($cartItem));
        $strategy->setImportExportContext($context);

        $execution->expects($this->exactly(2))
            ->method('get')
            ->with($this->isType('string'));
        $execution->expects($this->exactly(2))
            ->method('put')
            ->with($this->isType('string'), $this->isType('array'));

        $this->assertNull($strategy->process($cart));
    }
}
