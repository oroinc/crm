<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\ImportExport\Strategy;

use Oro\Component\Testing\Unit\EntityTrait;

use Oro\Bundle\ImportExportBundle\Field\DatabaseHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\Entity\IntegrationAwareInterface;
use Oro\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\GuestCustomerStrategyHelper;

class GuestCustomerStrategyHelperTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    /** @var  GuestCustomerStrategyHelper | \PHPUnit_Framework_MockObject_MockObject */
    protected $strategyHelper;

    /** @var  DatabaseHelper | \PHPUnit_Framework_MockObject_MockObject */
    protected $databaseHelper;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->databaseHelper = $this->createMock(DatabaseHelper::class);

        $this->strategyHelper = new GuestCustomerStrategyHelper($this->databaseHelper);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
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
     * @param IntegrationAwareInterface $parentEntity
     * @param $identityValues
     * @param $guestCustomerEmailList
     * @param $expectedResult
     */
    public function testUpdateIdentityValuesByParentEntity(
        IntegrationAwareInterface $parentEntity,
        $identityValues,
        $guestCustomerEmailList,
        $expectedResult
    ) {
        if (!empty($identityValues)) {
            $transport = $this->getTransport($guestCustomerEmailList);
            $channel = $parentEntity->getChannel();
            $channel->setTransport($transport);

            $this->databaseHelper->expects($this->once())
                ->method('findOneBy')
                ->with(Channel::class, ['id' => $channel->getId()])
                ->willReturn($channel);
        } else {
            $this->databaseHelper->expects($this->never())
                ->method('findOneBy');
        }

        $result = $this->strategyHelper->updateIdentityValuesByParentEntity($parentEntity, $identityValues);
        $this->assertEquals($result, $expectedResult);
    }

    public function parentEntityProvider()
    {
        return [
            'update context by order with firstname and email in context' => [
                'parentEntity' => $this->getParentEntity(Order::class, ['firstName' => 'Joe']),
                'context'                => ['email' => 'test@test.com'],
                'guestCustomerEmailList' => ['test@test.com'],
                'expectedIdentityValues' => [
                    'email'     => 'test@test.com',
                    'firstName' => 'Joe'
                ]
            ],
            'update context by cart with firstname and lastname and email in context' => [
                'parentEntity' => $this->getParentEntity(
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
                'parentEntity' => $this->getParentEntity(Order::class, ['firstName' => 'Joe']),
                'context'                => [],
                'guestCustomerEmailList' => ['test@test.com'],
                'expectedIdentityValues' => []
            ],
            'update context by cart with email not in list' => [
                'parentEntity' => $this->getParentEntity(
                    Cart::class,
                    ['firstName' => 'Joe', 'lastName' => 'Doe']
                ),
                'context'                => ['email' => 'test@test.com'],
                'guestCustomerEmailList' => ['test2@test.com'],
                'expectedIdentityValues' => [
                    'email'     => 'test@test.com'
                ]
            ]
        ];
    }

    private function getParentEntity($className, $attributes)
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
        $customer = new Customer();
        $customer->setFirstName('John')
            ->setLastName('Doe')
            ->setEmail('test@mail.com');
        $channel = $this->getEntity(Channel::class, ['id' => 1]);
        $customer->setChannel($channel);

        return $customer;
    }

    /**
     * @param string $sharedGuestEmailList
     *
     * @return \PHPUnit_Framework_MockObject_MockObject | MagentoTransport
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
