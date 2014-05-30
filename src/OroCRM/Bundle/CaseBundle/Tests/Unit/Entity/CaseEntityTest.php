<?php

namespace OroCRM\Bundle\CaseBundle\Tests\Unit\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use OroCRM\Bundle\CaseBundle\Entity\CaseEntity;

class CaseEntityTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        new CaseEntity();
    }

    /**
     * @dataProvider settersAndGettersDataProvider
     */
    public function testSettersAndGetters($property, $value)
    {
        $obj = new CaseEntity();

        $result = call_user_func_array(array($obj, 'set' . ucfirst($property)), array($value));
        $this->assertInstanceOf(get_class($obj), $result);
        $this->assertEquals($value, call_user_func_array(array($obj, 'get' . ucfirst($property)), array()));
    }

    public function settersAndGettersDataProvider()
    {
        $origin = $this->getMock('OroCRM\Bundle\CaseBundle\Entity\CaseOrigin');
        $origins = new ArrayCollection(array($origin));

        return array(
            array('id', 42),
            array('subject', 'Test subject'),
            array('description', 'Test Description'),
            array('owner', $this->getMock('Oro\Bundle\UserBundle\Entity\User')),
            array('workflowStep', $this->getMock('Oro\Bundle\WorkflowBundle\Entity\WorkflowStep')),
            array('workflowItem', $this->getMock('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')),
            array('reporter', $this->getMock('OroCRM\Bundle\CaseBundle\Entity\CaseReporter')),
            array('item', $this->getMock('OroCRM\Bundle\CaseBundle\Entity\CaseItem')),
            array('origins', $origins),
            array('createdAt', new \DateTime()),
            array('updatedAt', new \DateTime()),
            array('reportedOn', new \DateTime()),
            array('closedOn', new \DateTime())
        );
    }
}
