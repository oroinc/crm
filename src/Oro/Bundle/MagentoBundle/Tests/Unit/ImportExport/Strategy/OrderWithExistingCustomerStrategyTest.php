<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\ImportExport\Strategy;

use Akeneo\Bundle\BatchBundle\Item\ExecutionContext;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\ImportExport\Strategy\OrderWithExistingCustomerStrategy;
use Oro\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\GuestCustomerStrategyHelper;

class OrderWithExistingCustomerStrategyTest extends AbstractStrategyTest
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ContextInterface $context
     */
    protected $context;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|MagentoTransport $transport
     */
    protected $transport;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|Channel $channel
     */
    protected $channel;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|GuestCustomerStrategyHelper
     */
    protected $guestCustomerStrategyHelper;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ExecutionContext $execution
     */
    protected $execution;

    protected function setUp(): void
    {
        parent::setUp();

        $this->context = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Context\ContextInterface')
            ->getMock();

        $this->transport = $this->getMockBuilder('Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport')
            ->disableOriginalConstructor()
            ->getMock();

        $this->channel = $this->getMockBuilder('Oro\Bundle\IntegrationBundle\Entity\Channel')
            ->disableOriginalConstructor()
            ->getMock();

        $this->guestCustomerStrategyHelper = $this->createMock(GuestCustomerStrategyHelper::class);

        $this->execution = $this->getMockBuilder('Akeneo\Bundle\BatchBundle\Item\ExecutionContext')
            ->getMock();
    }

    /**
     * {@inheritdoc}
     */
    protected function getStrategy()
    {
        $strategy = new OrderWithExistingCustomerStrategy(
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

        $strategy->setGuestCustomerStrategyHelper($this->guestCustomerStrategyHelper);

        return $strategy;
    }

    public function testProcessFailed()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Execution context is not configured');

        $customer = new Customer();
        $customer->setOriginId(1);
        $channel = new Channel();
        $order = new Order();
        $cart = new Cart();
        $order->setCustomer($customer);
        $order->setChannel($channel);
        $order->setCart($cart);

        $strategy = $this->getStrategy();
        $strategy->setImportExportContext($this->context);
        $this->assertNull($strategy->process($order));
    }

    public function testProcessOrderWithExistingRegisteredCustomer()
    {
        $customer = new Customer();
        $customer->setOriginId(1);

        $this->transport->expects($this->once())
            ->method('getIsExtensionInstalled')
            ->will($this->returnValue(true));
        $this->channel->expects($this->once())
            ->method('getTransport')
            ->will($this->returnValue($this->transport));
        $order = new Order();
        $cart = new Cart();
        $cart->setOriginId(1);
        $order->setCustomer($customer);
        $order->setChannel($this->channel);
        $order->setCart($cart);

        $this->databaseHelper->expects($this->once())
            ->method('findOneByIdentity')
            ->with($this->channel)
            ->will($this->returnValue($this->channel));

        $strategy = $this->getStrategy();
        $this->jobExecution->expects($this->any())->method('getExecutionContext')
            ->will($this->returnValue($this->execution));
        $strategy->setStepExecution($this->stepExecution);

        $orderItemDate = ['customerId' => uniqid()];
        $this->context->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($orderItemDate));
        $strategy->setImportExportContext($this->context);

        $this->execution->expects($this->exactly(3))
            ->method('get')
            ->with($this->isType('string'));
        $this->execution->expects($this->exactly(3))
            ->method('put')
            ->with($this->isType('string'), $this->isType('array'));

        $this->assertNull($strategy->process($order));
    }

    public function testProcessOrderWithExistingGuestCustomer()
    {
        $customer = new Customer();
        $customer->setGuest(true);

        $this->transport->expects($this->once())
            ->method('getIsExtensionInstalled')
            ->will($this->returnValue(true));

        $this->channel->expects($this->once())
            ->method('getTransport')
            ->will($this->returnValue($this->transport));
        $order = new Order();
        $cart = new Cart();
        $cart->setOriginId(1);
        $order->setCustomer($customer);
        $order->setChannel($this->channel);
        $order->setCart($cart);

        $customerEmail = 'test@example.com';
        $order->setCustomerEmail($customerEmail);
        $customer->setChannel($this->channel);
        $this->databaseHelper->expects($this->any())
            ->method('findOneBy')
            ->will(
                $this->returnValueMap(
                    [
                        [
                            'Oro\Bundle\MagentoBundle\Entity\Customer',
                            [
                                'email' => $customerEmail,
                                'channel' => $this->channel
                            ],
                            $customer
                        ]
                    ]
                )
            );

        $this->databaseHelper->expects($this->once())
            ->method('findOneByIdentity')
            ->with($this->channel)
            ->will($this->returnValue($this->channel));

        $strategy = $this->getStrategy();
        $this->jobExecution->expects($this->any())->method('getExecutionContext')
            ->will($this->returnValue($this->execution));
        $strategy->setStepExecution($this->stepExecution);

        $orderItemDate = ['customerId' => uniqid()];
        $this->context->expects($this->once())
            ->method('getValue')
            ->will($this->returnValue($orderItemDate));
        $strategy->setImportExportContext($this->context);

        $this->execution->expects($this->exactly(2))
            ->method('get')
            ->with($this->isType('string'));
        $this->execution->expects($this->exactly(2))
            ->method('put')
            ->with($this->isType('string'), $this->isType('array'));

        $this->assertNull($strategy->process($order));
    }

    public function testProcessOrderWithNewGuestCustomer()
    {
        $customer = new Customer();
        $customer->setGuest(true);

        $this->transport->expects($this->once())
            ->method('getIsExtensionInstalled')
            ->will($this->returnValue(true));
        $this->transport->expects($this->once())
            ->method('getGuestCustomerSync')
            ->will($this->returnValue(true));

        $this->channel->expects($this->once())
            ->method('getTransport')
            ->will($this->returnValue($this->transport));
        $order = new Order();
        $cart = new Cart();
        $cart->setOriginId(1);
        $order->setCustomer($customer);
        $order->setChannel($this->channel);
        $order->setCart($cart);

        $customerEmail = 'new_guest@example.com';
        $order->setCustomerEmail($customerEmail);
        $order->setIsGuest(true);

        $customer->setChannel($this->channel);
        $this->databaseHelper->expects($this->any())
            ->method('findOneBy')
            ->will(
                $this->returnValueMap(
                    [
                        [
                            'Oro\Bundle\MagentoBundle\Entity\Customer',
                            [
                                'email' => $customerEmail,
                                'channel' => $this->channel
                            ],
                            null
                        ]
                    ]
                )
            );

        $this->databaseHelper->expects($this->once())
            ->method('findOneByIdentity')
            ->with($this->channel)
            ->will($this->returnValue($this->channel));

        $strategy = $this->getStrategy();

        $this->jobExecution->expects($this->any())->method('getExecutionContext')
            ->will($this->returnValue($this->execution));
        $strategy->setStepExecution($this->stepExecution);

        $orderItemDate = ['customerId' => uniqid()];
        $this->context->expects($this->any())
            ->method('getValue')
            ->will($this->returnValue($orderItemDate));
        $strategy->setImportExportContext($this->context);

        $this->execution->expects($this->exactly(3))
            ->method('get')
            ->with($this->isType('string'));
        $this->execution->expects($this->exactly(3))
            ->method('put')
            ->with($this->isType('string'), $this->isType('array'));

        $this->assertNull($strategy->process($order));
    }
}
