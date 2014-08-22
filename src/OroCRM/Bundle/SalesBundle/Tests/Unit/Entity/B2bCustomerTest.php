<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Entity;

use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;

class B2bCustomerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getSetDataProvider
     */
    public function testGetSet($property, $value, $expected)
    {
        $obj = new B2bCustomer();

        call_user_func_array(array($obj, 'set' . ucfirst($property)), array($value));
        $this->assertEquals($expected, call_user_func_array(array($obj, 'get' . ucfirst($property)), array()));
    }

    /**
     * @return array
     */
    public function getSetDataProvider()
    {
        $account = $this->getMockBuilder('OroCRM\Bundle\AccountBundle\Entity\Account')
            ->disableOriginalConstructor()
            ->getMock();

        return [
            'account' => ['account', $account, $account],
        ];
    }
}
