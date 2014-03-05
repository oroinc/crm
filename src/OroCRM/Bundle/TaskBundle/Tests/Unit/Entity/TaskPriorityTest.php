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
     * @dataProvider settersAndGettersDataProvider
     */
    public function testSettersAndGetters($property, $value)
    {
        $obj = new TaskPriority('low');

        call_user_func_array(array($obj, 'set' . ucfirst($property)), array($value));
        $this->assertEquals($value, call_user_func_array(array($obj, 'get' . ucfirst($property)), array()));
    }

    public function testGetName()
    {
        $expected = 'low';
        $entity = new TaskPriority($expected);
        $this->assertEquals($expected, $entity->getName());
    }

    public function testToString()
    {
        $expected = 'Low test';
        $entity = new TaskPriority('low');
        $entity->setLabel($expected);
        $this->assertEquals($expected, (string)$entity);
    }

    public function settersAndGettersDataProvider()
    {
        return array(
            array('label', 'Test LOW')
        );
    }
}
