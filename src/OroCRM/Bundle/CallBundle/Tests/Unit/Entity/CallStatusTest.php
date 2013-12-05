<?php

namespace OroCRM\Bundle\CallBundle\Tests\Unit\Entity;

use OroCRM\Bundle\CallBundle\Entity\CallStatus;

class CallStatusTest extends \PHPUnit_Framework_TestCase
{
    public function testGetName()
    {
        $name = 'status';
        $obj = new CallStatus($name);
        $this->assertEquals($name, $obj->getName());
    }

    /**
     * @dataProvider getSetDataProvider
     */
    public function testGetSet($property, $value, $expected)
    {
        $obj = new CallStatus('status');

        call_user_func_array(array($obj, 'set' . ucfirst($property)), array($value));
        $this->assertEquals($expected, call_user_func_array(array($obj, 'get' . ucfirst($property)), array()));
    }

    public function getSetDataProvider()
    {
        return array(
            'status' => array('label', 'My status', 'My status'),
        );
    }
}
