<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\ImportExport\Strategy;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\AddressBundle\Entity\Country;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel;

use OroCRM\Bundle\MagentoBundle\Entity\CartAddress;
use OroCRM\Bundle\MagentoBundle\Entity\CartItem;
use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\CartStatus;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\CartStrategy;

class CartStrategyTest extends AbstractStrategyTest
{
    /**
     * {@inheritdoc}
     */
    protected function getStrategy()
    {
        $strategy = new CartStrategy(
            $this->eventDispatcher,
            $this->strategyHelper,
            $this->fieldHelper,
            $this->databaseHelper
        );

        $strategy->setOwnerHelper($this->defaultOwnerHelper);
        $strategy->setLogger($this->logger);
        $strategy->setChannelHelper($this->channelHelper);
        $strategy->setAddressHelper($this->addressHelper);

        return $strategy;
    }

    /**
     * @param mixed $expected
     * @param mixed $entity
     * @param mixed $databaseEntity
     *
     * @dataProvider contactInfoDataProvider
     */
    public function testContactInfo($expected, $entity, $databaseEntity = null)
    {
        $strategy = $this->getStrategy();

        /** @var \PHPUnit_Framework_MockObject_MockObject|ContextInterface $context */
        $context = $this->getMock('Oro\Bundle\ImportExportBundle\Context\ContextInterface');
        $strategy->setImportExportContext($context);
        $strategy->setEntityName('OroCRM\Bundle\MagentoBundle\Entity\Cart');

        $execution = $this->getMock('Akeneo\Bundle\BatchBundle\Item\ExecutionContext');
        $this->jobExecution->expects($this->any())->method('getExecutionContext')
            ->will($this->returnValue($execution));
        $strategy->setStepExecution($this->stepExecution);

        $this->databaseHelper->expects($this->once())->method('getEntityReference')->will($this->returnArgument(0));
        $this->databaseHelper->expects($this->once())->method('findOneByIdentity')->willReturn($databaseEntity);

        $this->assertEquals($expected, $strategy->process($entity));
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
                $this->getEntity(['itemsCount' => 1, 'email' => 'user@example.com'])
            ],
            'dont change status' => [
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
                        'customer' => $this->getCustomer('user@example.com')
                    ]
                ),
                'entity' => $this->getEntity(
                    [
                        'itemsCount' => 1,
                        'email' => 'user@example.com',
                        'customer' => $this->getCustomer()
                    ]
                ),
                'databaseEntity' => $this->getEntity(
                    [
                        'itemsCount' => 1,
                        'email' => 'database@example.com',
                        'customer' => $this->getCustomer('database@example.com')
                    ]
                )
            ],
            'add new cart item and remove old' => [
                'expected' => $this->getEntity(
                    [
                        'itemsCount' => 1,
                        'email' => 'user@example.com',
                        'cartItems' => new ArrayCollection([$this->getCartItem(1)])
                    ]
                ),
                'entity' => $this->getEntity(
                    [
                        'itemsCount' => 1,
                        'email' => 'user@example.com',
                        'cartItems' => new ArrayCollection([$this->getCartItem(1)])
                    ]
                ),
                'databaseEntity' => $this->getEntity(
                    [
                        'itemsCount' => 1,
                        'email' => 'user@example.com',
                        'cartItems' => new ArrayCollection([$this->getCartItem(2)])
                    ]
                )
            ],
            'add new cart item and keep old one' => [
                'expected' => $this->getEntity(
                    [
                        'itemsCount' => 1,
                        'email' => 'user@example.com',
                        'cartItems' => new ArrayCollection([$this->getCartItem(1), $this->getCartItem(2)])
                    ]
                ),
                'entity' => $this->getEntity(
                    [
                        'itemsCount' => 1,
                        'email' => 'user@example.com',
                        'cartItems' => new ArrayCollection([$this->getCartItem(1), $this->getCartItem(2)])
                    ]
                ),
                'databaseEntity' => $this->getEntity(
                    [
                        'itemsCount' => 1,
                        'email' => 'user@example.com',
                        'cartItems' => new ArrayCollection([$this->getCartItem(2)])
                    ]
                )
            ],
            'update existing address' => [
                'expected' => $this->getEntity(
                    [
                        'itemsCount' => 1,
                        'email' => 'user@example.com',
                        'shippingAddress' => $this->getAddress('US')
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

        if ($countryCode) {
            $address->setCountry(new Country($countryCode));
        }

        return $address;
    }

    /**
     * @param string $email
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

        return $cart;
    }
}
