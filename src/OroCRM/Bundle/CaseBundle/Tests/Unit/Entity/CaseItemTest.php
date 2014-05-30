<?php

namespace OroCRM\Bundle\CaseBundle\Tests\Unit\Entity;

use OroCRM\Bundle\CaseBundle\Entity\CaseItem;

class CaseItemTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        new CaseItem();
    }
    /**
     * @dataProvider settersAndGettersDataProvider
     */
    public function testSettersAndGetters($property, $value)
    {
        $obj = new CaseItem();

        $result = call_user_func_array(array($obj, 'set' . ucfirst($property)), array($value));
        $this->assertInstanceOf(get_class($obj), $result);
        $this->assertEquals($value, call_user_func_array(array($obj, 'get' . ucfirst($property)), array()));
    }

    public function settersAndGettersDataProvider()
    {
        return array(
            array('id', 42),
            array('lead', $this->getMock('OroCRM\Bundle\SalesBundle\Entity\Lead')),
            array('opportunity', $this->getMock('OroCRM\Bundle\SalesBundle\Entity\Opportunity')),
            array('cart', $this->getMock('OroCRM\Bundle\MagentoBundle\Entity\Cart')),
            array('order', $this->getMock('OroCRM\Bundle\MagentoBundle\Entity\Order'))
        );
    }
}
