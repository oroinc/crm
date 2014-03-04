<?php

namespace OroCRM\Bundle\TaskBundle\Tests\Unit\Form\Type;


use OroCRM\Bundle\TaskBundle\Form\Type\TaskType;

class TaskTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TaskType
     */
    protected $formType;

    protected function setUp()
    {
        $this->formType = new TaskType();
    }

    public function testGetName()
    {
        $this->assertEquals('orocrm_task', $this->formType->getName());
    }
}
