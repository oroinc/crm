<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Entity;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\CartStatus;

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
        $testStatus = new CartStatus('test');

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
        );
    }
}
