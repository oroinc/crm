<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\CartAddress;
use OroCRM\Bundle\MagentoBundle\Entity\CartItem;
use OroCRM\Bundle\MagentoBundle\Entity\CartStatus;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Store;

class CartTest extends AbstractEntityTestCase
{
    /** @var Cart */
    protected $entity;

    /**
     * {@inheritDoc}
     */
    public function getEntityFQCN()
    {
        return 'OroCRM\Bundle\MagentoBundle\Entity\Cart';
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
        $owner               = $this->getMock('Oro\Bundle\UserBundle\Entity\User');
        $organization        = $this->getMock('Oro\Bundle\OrganizationBundle\Entity\Organization');

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
