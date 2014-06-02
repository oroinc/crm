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
        $origin = $this->getMock('OroCRM\Bundle\CaseBundle\Entity\CaseOrigin');

        return [
            ['id', 42],
            ['subject', 'Test subject'],
            ['description', 'Test Description'],
            ['owner', $this->getMock('Oro\Bundle\UserBundle\Entity\User')],
            ['workflowStep', $this->getMock('Oro\Bundle\WorkflowBundle\Entity\WorkflowStep')],
            ['workflowItem', $this->getMock('Oro\Bundle\WorkflowBundle\Entity\WorkflowItem')],
            ['reporter', $this->getMock('OroCRM\Bundle\CaseBundle\Entity\CaseReporter')],
            ['item', $this->getMock('OroCRM\Bundle\CaseBundle\Entity\CaseItem')],
            ['origin', $origin],
            ['createdAt', new \DateTime()],
            ['updatedAt', new \DateTime()],
            ['reportedAt', new \DateTime()],
            ['closedAt', new \DateTime()],
        ];
    }
}
