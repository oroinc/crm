<?php

namespace Oro\Bundle\AddressBundle\Tests\Entity;

use Oro\Bundle\AddressBundle\Entity\Address;

class AddressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider
     * @param string $property
     */
    public function testSettersAndGetters($property)
    {
        $obj = new Address();
        $value = 'testValue';

        call_user_func_array(array($obj, 'set' . ucfirst($property)), array($value));
        $this->assertEquals($value, call_user_func_array(array($obj, 'get' . ucfirst($property)), array()));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return array(
            array('street'),
            array('street2'),
            array('city'),
            array('state'),
            array('postalCode'),
            array('country'),
            array('mark'),
        );
    }
}
