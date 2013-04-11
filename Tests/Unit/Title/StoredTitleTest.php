<?php

namespace Oro\Bundle\NavigationBundle\Tests\Unit\Title;

use Oro\Bundle\NavigationBundle\Title\StoredTitle;

class StoredTitleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider
     * @param string $property
     * @param bool $isArray
     */
    public function testSettersAndGetters($property, $isArray = false)
    {
        $obj = new StoredTitle();
        if ($isArray) {
            $value = array('testKey' => 'testValue');
        } else {
            $value = 'testValue';
        }

        call_user_func_array(array($obj, 'set' . $property), array($value));
        $this->assertEquals($value, call_user_func_array(array($obj, 'get' . $property), array($property)));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return array(
            array('params', true),
            array('template'),
            array('prefix'),
            array('suffix')
        );
    }
}
