<?php

namespace OroCRM\Bundle\CallBundle\Tests\Unit\Entity;

use OroCRM\Bundle\CallBundle\Entity\CallDirection;

class CallDirectionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetName()
    {
        $name = 'direction';
        $obj = new CallDirection($name);
        $this->assertEquals($name, $obj->getName());
    }

    /**
     * @dataProvider getSetDataProvider
     */
    public function testGetSet($property, $value, $expected)
    {
        $obj = new CallDirection('direction');

        call_user_func_array(array($obj, 'set' . ucfirst($property)), array($value));
        $this->assertEquals($expected, call_user_func_array(array($obj, 'get' . ucfirst($property)), array()));
    }

    public function getSetDataProvider()
    {
        return array(
            'direction' => array('Label', 'My direction', 'My direction'),
        );
    }
}
