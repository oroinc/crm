<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\Entity;

use OroCRM\Bundle\ContactBundle\Entity\Group;

class GroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Group
     */
    protected $group;

    protected function setUp()
    {
        $this->group = new Group();
    }

    public function testConstructor()
    {
        $this->assertNull($this->group->getLabel());

        $group = new Group('Label');
        $this->assertEquals('Label', $group->getLabel());
    }

    public function testLabel()
    {
        $this->assertNull($this->group->getLabel());
        $this->group->setLabel('Label');
        $this->assertEquals('Label', $this->group->getLabel());
        $this->assertEquals('Label', $this->group->__toString());
    }
}
