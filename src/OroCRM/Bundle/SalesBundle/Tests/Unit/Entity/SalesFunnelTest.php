<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Entity;

use OroCRM\Bundle\SalesBundle\Entity\SalesFunnel;

class SalesFunnelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getSetDataProvider
     */
    public function testGetSet($property, $value, $expected)
    {
        $obj = new SalesFunnel();

        call_user_func_array(array($obj, 'set' . ucfirst($property)), array($value));
        $this->assertEquals($expected, call_user_func_array(array($obj, 'get' . ucfirst($property)), array()));
    }

    public function getSetDataProvider()
    {
        $now = new \DateTime('now');
        $lead = $this->getMockBuilder('OroCRM\Bundle\SalesBundle\Entity\Lead')
            ->disableOriginalConstructor()
            ->getMock();
        $opportunity = $this->getMockBuilder('OroCRM\Bundle\SalesBundle\Entity\Opportunity')
            ->disableOriginalConstructor()
            ->getMock();
        $user = $this->getMockBuilder('Oro\Bundle\UserBundle\Entity\User')
            ->disableOriginalConstructor()
            ->getMock();
        $workflowItem = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')
            ->disableOriginalConstructor()
            ->getMock();
        $workflowStep = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Entity\WorkflowStep')
            ->disableOriginalConstructor()
            ->getMock();

        return array(
            'startDate' => array('startDate', $now, $now),
            'lead' => array('lead', $lead, $lead),
            'opportunity' => array('opportunity', $opportunity, $opportunity),
            'owner' => array('owner', $user, $user),
            'workflowItem' => array('workflowItem', $workflowItem, $workflowItem),
            'workflowStep' => array('workflowStep', $workflowStep, $workflowStep),
            'createdAt' => array('createdAt', $now, $now),
            'updatedAt' => array('updatedAt', $now, $now),
        );
    }

    public function testBeforeSave()
    {
        $obj = new SalesFunnel();
        $this->assertNull($obj->getCreatedAt());
        $this->assertNull($obj->getUpdatedAt());
        $obj->beforeSave();

        $this->assertInstanceOf('\DateTime', $obj->getCreatedAt());
        $this->assertNull($obj->getUpdatedAt());
    }

    public function testBeforeUpdate()
    {
        $obj = new SalesFunnel();
        $this->assertNull($obj->getCreatedAt());
        $this->assertNull($obj->getUpdatedAt());
        $obj->beforeUpdate();

        $this->assertInstanceOf('\DateTime', $obj->getUpdatedAt());
        $this->assertNull($obj->getCreatedAt());
    }
}
