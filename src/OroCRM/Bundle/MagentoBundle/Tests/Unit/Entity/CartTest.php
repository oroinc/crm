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
            'status'  => array('status', $testStatus, $testStatus),
        );
    }
}
