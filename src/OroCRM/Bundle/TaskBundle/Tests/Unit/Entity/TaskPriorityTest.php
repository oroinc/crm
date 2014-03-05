<?php

namespace OroCRM\Bundle\TaskBundle\Tests\Unit\Entity;

use OroCRM\Bundle\TaskBundle\Entity\TaskPriority;

class TaskPriorityTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        new TaskPriority('low');
    }

    /**
     * @dataProvider provider
     * @param string $property
     * @param mixed  $value
     */
    public function testSettersAndGetters($property, $value)
    {
        $obj = new TaskPriority('low');

        call_user_func_array(array($obj, 'set' . ucfirst($property)), array($value));
        $this->assertEquals($value, call_user_func_array(array($obj, 'get' . ucfirst($property)), array()));
    }

    public function provider()
    {
        return array(
            array('label', 'Test LOW')
        );
    }
}
