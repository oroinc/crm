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
    /**
     * @var Cart
     */
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

        return array(
            'status'            => array('status', $testStatus, $testStatus),
            'storeToQuoteRate'  => array('storeToQuoteRate', 1, 1),
            'storeToBaseRate'   => array('storeToBaseRate', 1, 1),
            'storeCurrencyCode' => array('storeCurrencyCode', 'USD', 'USD'),
            'baseCurrencyCode'  => array('baseCurrencyCode', 'USD', 'USD'),
            'paymentDetails'    => array('paymentDetails', '', ''),
            'itemsCount'        => array('itemsCount', 1, 1),
            'isGuest'           => array('isGuest', 1, 1),
            'giftMessage'       => array('giftMessage', 1, 1),
            'quoteCurrencyCode' => array('quoteCurrencyCode', 'USD', 'USD'),
            'subTotal'          => array('subTotal', 11.12, 11.12),
            'itemsQty'          => array('itemsQty', 3, 3),
            'email'             => array('email', 'test@example.com', 'test@example.com'),
            'shippingAddress'   => array('shippingAddress', $testShippingAddress, $testShippingAddress),
            'billingAddress'    => array('billingAddress', $testBillingAddress, $testBillingAddress),
            'customer'          => array('customer', $testCustomer, $testCustomer),
            'cartItems'         => array('cartItems', $testItemsCollection, $testItemsCollection),
            'store'             => array('store', $testStore, $testStore)
        );
    }
}
