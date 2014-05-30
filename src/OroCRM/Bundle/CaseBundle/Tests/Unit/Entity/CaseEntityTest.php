<?php

namespace OroCRM\Bundle\CaseBundle\Tests\Unit\Entity;

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

        call_user_func_array(array($obj, 'set' . ucfirst($property)), array($value));
        $this->assertEquals($value, call_user_func_array(array($obj, 'get' . ucfirst($property)), array()));
    }

    public function settersAndGettersDataProvider()
    {
        return array(
            array('id', 42),
            array('subject', 'Test subject'),
            array('description', 'Test Description'),
            array('emailAddress', 'alex@gmail.com'),
            array('otherContact', 'fax: 100092304'),
            array('phone', '100092304'),
            array('web', 'alex.com'),
            array('owner', $this->getMock('Oro\Bundle\UserBundle\Entity\User')),
            array('workflowStep', $this->getMock('Oro\Bundle\WorkflowBundle\Entity\WorkflowStep')),
            array('workflowItem', $this->getMock('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')),
            array('reporter', $this->getMock('Oro\Bundle\UserBundle\Entity\User')),
            array('reporterContact', $this->getMock('OroCRM\Bundle\ContactBundle\Entity\Contact')),
            array('reporterCustomer', $this->getMock('OroCRM\Bundle\MagentoBundle\Entity\Customer')),
            array('relatedLead', $this->getMock('OroCRM\Bundle\SalesBundle\Entity\Lead')),
            array('relatedOpportunity', $this->getMock('OroCRM\Bundle\SalesBundle\Entity\Opportunity')),
            array('relatedCart', $this->getMock('OroCRM\Bundle\MagentoBundle\Entity\Cart')),
            array('relatedOrder', $this->getMock('OroCRM\Bundle\MagentoBundle\Entity\Order')),
            array('createdAt', new \DateTime()),
            array('updatedAt', new \DateTime()),
            array('reportedOn', new \DateTime()),
            array('closedOn', new \DateTime())
        );
    }
}
