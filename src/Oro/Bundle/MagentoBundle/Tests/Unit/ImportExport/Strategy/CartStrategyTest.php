<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\ImportExport\Strategy;

use Akeneo\Bundle\BatchBundle\Item\ExecutionContext;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Entity\CartAddress;
use Oro\Bundle\MagentoBundle\Entity\CartItem;
use Oro\Bundle\MagentoBundle\Entity\CartStatus;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\ImportExport\Strategy\CartStrategy;
use Oro\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\GuestCustomerStrategyHelper;
use Oro\Bundle\OrganizationBundle\Ownership\EntityOwnershipAssociationsSetter;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;

class CartStrategyTest extends AbstractStrategyTest
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
     * @var \PHPUnit\Framework\MockObject\MockObject|ExecutionContext $execution
     */
    protected $execution;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|GuestCustomerStrategyHelper
     */
    protected $guestCustomerStrategyHelper;

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
        $strategy = new CartStrategy(
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

        $strategy->setOwnerHelper($this->defaultOwnerHelper);
        $strategy->setLogger($this->logger);
        $strategy->setChannelHelper($this->channelHelper);
        $strategy->setAddressHelper($this->addressHelper);
        $strategy->setGuestCustomerStrategyHelper($this->guestCustomerStrategyHelper);
        $strategy->setOwnershipSetter($this->createMock(EntityOwnershipAssociationsSetter::class));

        return $strategy;
    }

    /**
     * @param mixed $expected
     * @param Cart  $entity
     * @param mixed $databaseEntity
     *
     * @dataProvider contactInfoDataProvider
     */
    public function testContactInfo($expected, Cart $entity, $databaseEntity = null)
    {
        $strategy = $this->getStrategy();
        $this->jobExecution->expects($this->any())->method('getExecutionContext')
            ->will($this->returnValue($this->execution));
        $strategy->setStepExecution($this->stepExecution);
        $strategy->setImportExportContext($this->context);
        $strategy->setEntityName('Oro\Bundle\MagentoBundle\Entity\Cart');
        $strategy->setOwnershipSetter($this->createMock(EntityOwnershipAssociationsSetter::class));


        $reflection = new \ReflectionProperty(get_class($strategy), 'existingEntity');
        $reflection->setAccessible(true);
        $reflection->setValue($strategy, $entity);

        $this->transport->expects($this->any())
            ->method('getGuestCustomerSync')
            ->will($this->returnValue(true));

        $this->channel->expects($this->any())
            ->method('getTransport')
            ->will($this->returnValue($this->transport));

        $this->databaseHelper->expects($this->once())
            ->method('getEntityReference')
            ->will($this->returnArgument(0));

        $this->databaseHelper->expects($this->any())
            ->method('findOneByIdentity')
            ->will(
                $this->returnValueMap(
                    [
                        [
                            $entity->getChannel(),
                            $this->channel
                        ],
                        [
                            $entity,
                            $databaseEntity
                        ]
                    ]
                )
            );

        $this->guestCustomerStrategyHelper->expects($this->any())
            ->method('findExistingGuestCustomerByContext')
            ->willReturn($expected);

        $customer = null;
        if (is_object($databaseEntity)) {
            $customer = $databaseEntity->getCustomer();
        }
        $this->databaseHelper->expects($this->any())
            ->method('findOneBy')
            ->will(
                $this->returnValueMap(
                    [
                        [
                            'Oro\Bundle\MagentoBundle\Entity\Customer',
                            [
                                'channel' => $entity->getChannel(),
                                'email' => $entity->getEmail()
                            ],
                            $customer
                        ]
                    ]
                )
            );

        $actualEntity = $strategy->process($entity);
        if ($actualEntity) {
            $expected->setImportedAt($actualEntity->getImportedAt());
            $expected->setSyncedAt($actualEntity->getSyncedAt());
        }

        $this->assertEquals($expected, $actualEntity);
    }

    /**
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function contactInfoDataProvider()
    {
        return [
            'items count' => [null, $this->getEntity()],
            'without contact info' => [null, $this->getEntity(['itemsCount' => 1])],
            'email' => [
                $this->getEntity(['itemsCount' => 1, 'email' => 'user@example.com']),
                $this->getEntity(['itemsCount' => 1, 'email' => 'user@example.com']),

            ],
            'do not change status' => [
                $this->getEntity(
                    ['itemsCount' => 1, 'email' => 'user@example.com', 'status' => new CartStatus('custom')]
                ),
                $this->getEntity(
                    ['itemsCount' => 1, 'email' => 'user@example.com', 'status' => new CartStatus('custom')]
                ),
                $this->getEntity(
                    ['itemsCount' => 1, 'email' => 'user@example.com', 'status' => new CartStatus('custom')]
                )
            ],
            'change status' => [
                'expected' => $this->getEntity(
                    ['itemsCount' => 1, 'email' => 'user@example.com', 'status' => new CartStatus('expired')]
                ),
                'entity' => $this->getEntity(
                    ['itemsCount' => 1, 'email' => 'user@example.com', 'status' => new CartStatus('expired')]
                ),
                'databaseEntity' => $this->getEntity(
                    ['itemsCount' => 1, 'email' => 'user@example.com', 'status' => new CartStatus('open')]
                )
            ],
            'update customer email' => [
                'expected' => $this->getEntity(
                    [
                        'itemsCount' => 1,
                        'email' => 'user@example.com',
                        'customer' => $this->getCustomer('user@example.com'),
                        'isGuest'  => false
                    ]
                ),
                'entity' => $this->getEntity(
                    [
                        'itemsCount' => 1,
                        'email' => 'user@example.com',
                        'customer' => $this->getCustomer(),
                        'isGuest'  => false
                    ]
                ),
                'databaseEntity' => $this->getEntity(
                    [
                        'itemsCount' => 1,
                        'email' => 'database@example.com',
                        'customer' => $this->getCustomer('database@example.com'),
                        'isGuest'  => false
                    ]
                )
            ],
            'update customer email for guest cart' => [
                'expected' => $this->getEntity(
                    [
                        'itemsCount' => 1,
                        'email' => 'user@example.com',
                        'customer' => $this->getCustomer('user@example.com'),
                        'isGuest'  => true
                    ]
                ),
                'entity' => $this->getEntity(
                    [
                        'itemsCount' => 1,
                        'email' => 'user@example.com',
                        'customer' => $this->getCustomer(),
                        'isGuest'  => true
                    ]
                ),
                'databaseEntity' => $this->getEntity(
                    [
                        'itemsCount' => 1,
                        'email' => 'database@example.com',
                        'customer' => $this->getCustomer('database@example.com'),
                        'isGuest'  => true
                    ]
                )
            ],
            'add new cart item and remove old' => [
                'expected' => $this->getEntity(
                    [
                        'itemsCount' => 1,
                        'email' => 'user@example.com',
                        'cartItems' => new ArrayCollection([$this->getCartItem(1)]),
                        'itemsQty' => 1,
                    ]
                ),
                'entity' => $this->getEntity(
                    [
                        'itemsCount' => 1,
                        'email' => 'user@example.com',
                        'cartItems' => new ArrayCollection([$this->getCartItem(1)]),
                        'itemsQty' => 1,
                    ]
                ),
                'databaseEntity' => $this->getEntity(
                    [
                        'itemsCount' => 1,
                        'email' => 'user@example.com',
                        'cartItems' => new ArrayCollection([$this->getCartItem(2)]),
                        'itemsQty' => 1,
                    ]
                )
            ],
            'add new cart item and keep old one' => [
                'expected' => $this->getEntity(
                    [
                        'itemsCount' => 1,
                        'email' => 'user@example.com',
                        'cartItems' => new ArrayCollection([$this->getCartItem(1), $this->getCartItem(2)]),
                        'itemsQty' => 2,
                    ]
                ),
                'entity' => $this->getEntity(
                    [
                        'itemsCount' => 1,
                        'email' => 'user@example.com',
                        'cartItems' => new ArrayCollection([$this->getCartItem(1), $this->getCartItem(2)]),
                        'itemsQty' => 2,
                    ]
                ),
                'databaseEntity' => $this->getEntity(
                    [
                        'itemsCount' => 1,
                        'email' => 'user@example.com',
                        'cartItems' => new ArrayCollection([$this->getCartItem(2)]),
                        'itemsQty' => 1,
                    ]
                )
            ],
            'update existing address' => [
                'expected' => $this->getEntity(
                    [
                        'itemsCount' => 1,
                        'email' => 'user@example.com',
                        'shippingAddress' => $this->getAddress('US')->setCountryText(null)
                    ]
                ),
                'entity' => $this->getEntity(
                    [
                        'itemsCount' => 1,
                        'email' => 'user@example.com',
                        'shippingAddress' => $this->getAddress('US')
                    ]
                ),
                'databaseEntity' => $this->getEntity(
                    [
                        'itemsCount' => 1,
                        'email' => 'user@example.com',
                        'shippingAddress' => $this->getAddress('US')
                    ]
                )
            ],
            'drop without country' => [
                'expected' => $this->getEntity(
                    [
                        'itemsCount' => 1,
                        'email' => 'user@example.com',
                        'billingAddress' => null
                    ]
                ),
                'entity' => $this->getEntity(
                    [
                        'itemsCount' => 1,
                        'email' => 'user@example.com',
                        'billingAddress' => $this->getAddress()
                    ]
                )
            ]
        ];
    }

    /**
     * Test setting removed field on removed cart items
     */
    public function testUpdateRemovedCartItems()
    {
        $channel = new Channel();

        $cartItem1 = new CartItem();
        $cartItem1->setName('Cart Item 1');

        $cartItem2 = new CartItem();
        $cartItem2->setName('Cart Item 2');

        $cartItem3 = new CartItem();
        $cartItem3->setName('Cart Item 3');

        $existingCartItems = new ArrayCollection();
        $existingCartItems->add($cartItem1);
        $existingCartItems->add($cartItem2);

        $existingCart = new Cart();
        $existingCart->setCartItems($existingCartItems);
        $existingCart->setChannel($channel);
        $existingCart->setItemsQty(2);

        $newCartItems = new ArrayCollection();
        $newCartItems->add($cartItem2);
        $newCartItems->add($cartItem3);

        $newCart = new Cart();
        $newCart->setCartItems($newCartItems);
        $newCart->setChannel($channel);
        $newCart->setItemsQty(2);

        $this->databaseHelper->expects($this->once())
            ->method('findOneByIdentity')
            ->will($this->returnValue($existingCart));

        $this->databaseHelper->expects($this->once())
            ->method('getEntityReference')
            ->will($this->returnValue($channel));

        $this->databaseHelper->expects($this->once())
            ->method('getIdentifier')
            ->will($this->returnValue('identifier'));

        $this->databaseHelper->expects($this->once())
            ->method('find')
            ->with('Oro\Bundle\MagentoBundle\Entity\Cart', 'identifier')
            ->will($this->returnValue($newCart));

        $strategy = $this->getStrategy();
        $strategy->setImportExportContext($this->context);
        $strategy->setEntityName('Oro\Bundle\MagentoBundle\Entity\Cart');
        $this->jobExecution->expects($this->any())->method('getExecutionContext')
            ->will($this->returnValue($this->execution));
        $strategy->setStepExecution($this->stepExecution);

        $strategy->process($existingCart);

        $this->assertTrue($cartItem1->isRemoved());
        $this->assertFalse($cartItem2->isRemoved());
        $this->assertFalse($cartItem3->isRemoved());
    }

    /**
     * @param int $originId
     *
     * @return CartItem
     */
    protected function getCartItem($originId)
    {
        $cartItem = new CartItem();
        $cartItem->setOriginId($originId);

        return $cartItem;
    }

    /**
     * @param string $countryCode
     * @return CartAddress
     */
    protected function getAddress($countryCode = null)
    {
        $address = new CartAddress();

        $address->setCountryText('Test');
        if ($countryCode) {
            $address->setCountry(new Country($countryCode));
        }

        return $address;
    }

    /**
     * @param string|null $email
     * @return Customer
     */
    protected function getCustomer($email = null)
    {
        $customer = new Customer();
        if ($email) {
            $customer->setEmail($email);
        }

        return $customer;
    }

    /**
     * @param array $properties
     *
     * @return Cart
     */
    protected function getEntity(array $properties = [])
    {
        $cart = new Cart();

        $channel = new Channel();
        $cart->setChannel($channel);

        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        foreach ($properties as $property => $value) {
            if ($value instanceof ArrayCollection) {
                foreach ($value as $item) {
                    try {
                        $propertyAccessor->setValue($item, 'cart', $cart);
                    } catch (NoSuchPropertyException $e) {
                    }
                }
            } elseif (is_object($value)) {
                try {
                    $propertyAccessor->setValue($value, 'cart', $cart);
                } catch (NoSuchPropertyException $e) {
                }
            }

            $propertyAccessor->setValue($cart, $property, $value);
        }

        if ($cart->getCustomer()) {
            $cart->getCustomer()->setChannel($channel);
        }

        return $cart;
    }
}
