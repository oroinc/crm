<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Entity\CartAddress;
use Oro\Bundle\MagentoBundle\Entity\CartItem;
use Oro\Bundle\MagentoBundle\Entity\CartStatus;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\Store;

class CartTest extends AbstractEntityTestCase
{
    /** @var Cart */
    protected $entity;

    /**
     * {@inheritDoc}
     */
    public function getEntityFQCN()
    {
        return 'Oro\Bundle\MagentoBundle\Entity\Cart';
    }

    public function testConstruct()
    {
        $this->assertNotEmpty($this->entity->getStatus());
        $this->assertEquals('open', $this->entity->getStatus()->getName());
    }

    /**
     * @return array
     */
    public function getSetDataProvider()
    {
        $testStatus          = new CartStatus('test');
        $testBillingAddress  = new CartAddress();
        $testShippingAddress = new CartAddress();
        $testCustomer        = new Customer();
        $testItemsCollection = new ArrayCollection([new CartItem()]);
        $testStore           = new Store();
        $owner               = $this->createMock('Oro\Bundle\UserBundle\Entity\User');
        $organization        = $this->createMock('Oro\Bundle\OrganizationBundle\Entity\Organization');

        return [
            'status'            => ['status', $testStatus, $testStatus],
            'storeToQuoteRate'  => ['storeToQuoteRate', 1, 1],
            'storeToBaseRate'   => ['storeToBaseRate', 1, 1],
            'storeCurrencyCode' => ['storeCurrencyCode', 'USD', 'USD'],
            'baseCurrencyCode'  => ['baseCurrencyCode', 'USD', 'USD'],
            'paymentDetails'    => ['paymentDetails', '', ''],
            'itemsCount'        => ['itemsCount', 1, 1],
            'isGuest'           => ['isGuest', 1, 1],
            'giftMessage'       => ['giftMessage', 1, 1],
            'quoteCurrencyCode' => ['quoteCurrencyCode', 'USD', 'USD'],
            'subTotal'          => ['subTotal', 11.12, 11.12],
            'itemsQty'          => ['itemsQty', 3, 3],
            'email'             => ['email', 'test@example.com', 'test@example.com'],
            'shippingAddress'   => ['shippingAddress', $testShippingAddress, $testShippingAddress],
            'billingAddress'    => ['billingAddress', $testBillingAddress, $testBillingAddress],
            'customer'          => ['customer', $testCustomer, $testCustomer],
            'cartItems'         => ['cartItems', $testItemsCollection, $testItemsCollection],
            'store'             => ['store', $testStore, $testStore],
            'statusMessage'     => ['statusMessage', 'some message', 'some message'],
            'owner'             => ['owner', $owner, $owner],
            'organization'      => ['organization', $organization, $organization]
        ];
    }
}
