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
    public function testSettersAndGetters($property, $value, $expected = null)
    {
        $entity = new CaseEntity();

        $method = 'set' . $property;
        if (method_exists($entity, $method)) {
            $result = $entity->$method($value);
        }

        $method = 'add' . rtrim($property, 's');
        if (method_exists($entity, $method)) {
            $result = $entity->$method($value);
        }

        $this->assertInstanceOf(get_class($entity), $result);

        $this->assertEquals($expected ? : $value, $entity->{'get' . $property}());
    }

    public function settersAndGettersDataProvider()
    {
        $origin = $this->getMockBuilder('OroCRM\Bundle\CaseBundle\Entity\CaseOrigin')
            ->disableOriginalConstructor()
            ->getMock();

        return array(
            array('id', 42),
            array('subject', 'Test subject'),
            array('description', 'Test Description'),
            array('owner', $this->getMock('Oro\Bundle\UserBundle\Entity\User')),
            array('workflowStep', $this->getMock('Oro\Bundle\WorkflowBundle\Entity\WorkflowStep')),
            array('workflowItem', $this->getMock('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')),
            array('origin', $origin),
            array('createdAt', new \DateTime()),
            array('updatedAt', new \DateTime()),
            array('reportedAt', new \DateTime()),
            array('closedAt', new \DateTime()),
            array('relatedLead', $this->getMock('OroCRM\Bundle\SalesBundle\Entity\Lead')),
            array('relatedOpportunity', $this->getMock('OroCRM\Bundle\SalesBundle\Entity\Opportunity')),
            array('relatedCart', $this->getMock('OroCRM\Bundle\MagentoBundle\Entity\Cart')),
            array('relatedOrder', $this->getMock('OroCRM\Bundle\MagentoBundle\Entity\Order')),
            array('reporter', $this->getMock('Oro\Bundle\UserBundle\Entity\User')),
            array('relatedContact', $this->getMock('OroCRM\Bundle\ContactBundle\Entity\Contact')),
            array('relatedCustomer', $this->getMock('OroCRM\Bundle\MagentoBundle\Entity\Customer'))
        );
    }
}
