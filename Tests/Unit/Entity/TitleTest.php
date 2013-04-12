<?php

namespace Oro\Bundle\NavigationBundle\Tests\Entity;

use Oro\Bundle\NavigationBundle\Entity\Title;

class TitleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider
     * @param string $property
     * @param bool $isArray
     */
    public function testSettersAndGetters($property, $isArray = false)
    {
        $obj = new Title();
        if ($isArray) {
            $value = array('testKey' => 'testValue');
        } else {
            $value = 'testValue';
        }

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
            array('route'),
            array('title'),
            array('isSystem')
        );
    }
}
