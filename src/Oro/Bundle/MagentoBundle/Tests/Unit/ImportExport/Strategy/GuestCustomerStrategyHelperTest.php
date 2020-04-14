<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\ImportExport\Strategy;

use Oro\Bundle\ImportExportBundle\Field\DatabaseHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\IntegrationAwareInterface;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\GuestCustomerStrategyHelper;
use Oro\Component\Testing\Unit\EntityTrait;

class GuestCustomerStrategyHelperTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var  GuestCustomerStrategyHelper | \PHPUnit\Framework\MockObject\MockObject */
    protected $strategyHelper;

    /** @var  DatabaseHelper | \PHPUnit\Framework\MockObject\MockObject */
    protected $databaseHelper;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->databaseHelper = $this->createMock(DatabaseHelper::class);

        $this->strategyHelper = new GuestCustomerStrategyHelper($this->databaseHelper);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        unset(
            $this->databaseHelper,
            $this->strategyHelper
        );
    }

    public function testCustomerChannelHasNoLoadedTransport()
    {
        $customer = $this->getCustomer();
        $channel = new Channel();
        $transport = $this->getTransport();
        $channel->setTransport($transport);

        $this->databaseHelper->expects($this->once())
            ->method('findOneBy')
            ->willReturn($channel);

        $this->strategyHelper->isGuestCustomerEmailInSharedList($customer);
    }

    public function testCustomerEmailIsInList()
    {
        $customer = $this->getCustomer();
        $transport = $this->getTransport(['test@mail.com', 'test2@mail.com']);
        $channel = $customer->getChannel();
        $channel->setTransport($transport);

        $this->databaseHelper->expects($this->once())
            ->method('findOneBy')
            ->willReturn($channel);

        $result = $this->strategyHelper->isGuestCustomerEmailInSharedList($customer);
        $this->assertTrue($result);
    }

    public function testCustomerEmailIsNotInList()
    {
        $customer = $this->getCustomer();
        $transport = $this->getTransport(['test2@mail.com', 'test3@mail.com']);
        $channel = $customer->getChannel();
        $channel->setTransport($transport);

        $this->databaseHelper->expects($this->once())
            ->method('findOneBy')
            ->willReturn($channel);

        $result = $this->strategyHelper->isGuestCustomerEmailInSharedList($customer);
        $this->assertFalse($result);
    }

    public function testUpdateSearchContextEmailInList()
    {
        $searchContext = ['email' => 'test@mail.com'];
        $customer = $this->getCustomer();
        $transport = $this->getTransport(['test@mail.com', 'test2@mail.com']);
        $channel = $customer->getChannel();
        $channel->setTransport($transport);

        $this->databaseHelper->expects($this->once())
            ->method('findOneBy')
            ->willReturn($channel);

        $result = $this->strategyHelper->getUpdatedSearchContextForGuestCustomers($customer, $searchContext);
        $searchContext = array_merge(
            $searchContext,
            ['firstName' => $customer->getFirstName(), 'lastName' => $customer->getLastName()]
        );
        $this->assertEquals($result, $searchContext);
    }

    public function testUpdateSearchContextEmailNotInList()
    {
        $searchContext = ['email' => 'test@mail.com'];
        $customer = $this->getCustomer();
        $transport = $this->getTransport(['test2@mail.com', 'test3@mail.com']);
        $channel = $customer->getChannel();
        $channel->setTransport($transport);

        $this->databaseHelper->expects($this->once())
            ->method('findOneBy')
            ->willReturn($channel);

        $result = $this->strategyHelper->getUpdatedSearchContextForGuestCustomers($customer, $searchContext);
        $this->assertEquals($result, $searchContext);
    }

    /**
     * @dataProvider parentEntityProvider
     *
     * @param IntegrationAwareInterface $entity
     * @param array $identityValues
     * @param array $guestCustomerEmailList
     * @param array $expectedResult
     */
    public function testUpdateIdentityValuesByCustomerOrParentEntity(
        IntegrationAwareInterface $entity,
        array $identityValues,
        array $guestCustomerEmailList,
        array $expectedResult
    ) {
        if (!empty($identityValues)) {
            $transport = $this->getTransport($guestCustomerEmailList);
            $channel = $entity->getChannel();
            $channel->setTransport($transport);

            $this->databaseHelper->expects($this->once())
                ->method('findOneBy')
                ->with(Channel::class, ['id' => $channel->getId()])
                ->willReturn($channel);
        } else {
            $this->databaseHelper->expects($this->never())
                ->method('findOneBy');
        }

        $result = $this->strategyHelper->updateIdentityValuesByCustomerOrParentEntity($entity, $identityValues);
        $this->assertEquals($result, $expectedResult);
    }

    /**
     * @return array
     */
    public function parentEntityProvider()
    {
        return [
            'update context by order with firstname and email in context' => [
                'parentEntity' => $this->getIntegrationAwareEntity(Order::class, ['firstName' => 'Joe']),
                'context'                => ['email' => 'test@test.com'],
                'guestCustomerEmailList' => ['test@test.com'],
                'expectedIdentityValues' => [
                    'email'     => 'test@test.com',
                    'firstName' => 'Joe'
                ]
            ],
            'update context by cart with firstname and lastname and email in context' => [
                'parentEntity' => $this->getIntegrationAwareEntity(
                    Cart::class,
                    ['firstName' => 'Joe', 'lastName' => 'Doe']
                ),
                'context'                => ['email' => 'test@test.com'],
                'guestCustomerEmailList' => ['test@test.com'],
                'expectedIdentityValues' => [
                    'email'     => 'test@test.com',
                    'firstName' => 'Joe',
                    'lastName'  => 'Doe'
                ]
            ],
            'update context by order without email' => [
                'parentEntity' => $this->getIntegrationAwareEntity(Order::class, ['firstName' => 'Joe']),
                'context'                => [],
                'guestCustomerEmailList' => ['test@test.com'],
                'expectedIdentityValues' => []
            ],
            'update context by cart with email not in list' => [
                'parentEntity' => $this->getIntegrationAwareEntity(
                    Cart::class,
                    ['firstName' => 'Joe', 'lastName' => 'Doe']
                ),
                'context'                => ['email' => 'test@test.com'],
                'guestCustomerEmailList' => ['test2@test.com'],
                'expectedIdentityValues' => [
                    'email'     => 'test@test.com'
                ]
            ],
            'update context by customer not in list' => [
                'parentEntity' => $this->getIntegrationAwareEntity(
                    Customer::class,
                    ['firstName' => 'Joe', 'lastName' => 'Doe']
                ),
                'context'                => ['email' => 'test@test.com'],
                'guestCustomerEmailList' => ['test2@test.com'],
                'expectedIdentityValues' => [
                    'email'     => 'test@test.com'
                ]
            ],
            'update context by customer in list' => [
                'parentEntity' => $this->getIntegrationAwareEntity(
                    Customer::class,
                    ['firstName' => 'Joe', 'lastName' => 'Doe']
                ),
                'context'                => ['email' => 'test@test.com'],
                'guestCustomerEmailList' => ['test@test.com'],
                'expectedIdentityValues' => [
                    'email'     => 'test@test.com',
                    'firstName' => 'Joe',
                    'lastName' => 'Doe'
                ]
            ]
        ];
    }

    /**
     * @param string $className
     * @param array $attributes
     *
     * @return object
     */
    private function getIntegrationAwareEntity($className, array $attributes = [])
    {
        $channel = $this->getEntity(Channel::class, ['id' => 1]);
        $attributes = array_merge(['channel' => $channel], $attributes);
        $parentEntity = $this->getEntity($className, $attributes);

        return $parentEntity;
    }

    /**
     * @return Customer
     */
    private function getCustomer()
    {
        /** @var Channel $channel */
        $channel = $this->getEntity(Channel::class, ['id' => 1]);
        $customer = new Customer();
        $customer->setFirstName('John')
            ->setLastName('Doe')
            ->setEmail('test@mail.com')
            ->setChannel($channel);

        return $customer;
    }

    /**
     * @param string $sharedGuestEmailList
     *
     * @return \PHPUnit\Framework\MockObject\MockObject | MagentoTransport
     */
    private function getTransport($sharedGuestEmailList = '')
    {
        $transport = $this->createMock(MagentoTransport::class);
        $transport->expects($this->once())
            ->method('getSharedGuestEmailList')
            ->willReturn($sharedGuestEmailList);

        return $transport;
    }
}
