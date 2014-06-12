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
        $entity = new CaseEntity();

        $method = 'set' . ucfirst($property);
        $result = $entity->$method($value);

        $this->assertInstanceOf(get_class($entity), $result);
        $this->assertEquals($value, $entity->{'get' . $property}());
    }

    public function settersAndGettersDataProvider()
    {
        $origin = $this->getMockBuilder('OroCRM\Bundle\CaseBundle\Entity\CaseOrigin')
            ->disableOriginalConstructor()
            ->getMock();

        $status = $this->getMockBuilder('OroCRM\Bundle\CaseBundle\Entity\CaseStatus')
            ->disableOriginalConstructor()
            ->getMock();

        $priority = $this->getMockBuilder('OroCRM\Bundle\CaseBundle\Entity\CasePriority')
            ->disableOriginalConstructor()
            ->getMock();

        return array(
            array('subject', 'Test subject'),
            array('description', 'Test Description'),
            array('resolution', 'Test Resolution'),
            array('assignedTo', $this->getMock('Oro\Bundle\UserBundle\Entity\User')),
            array('owner', $this->getMock('Oro\Bundle\UserBundle\Entity\User')),
            array('origin', $origin),
            array('status', $status),
            array('priority', $priority),
            array('createdAt', new \DateTime()),
            array('updatedAt', new \DateTime()),
            array('reportedAt', new \DateTime()),
            array('closedAt', new \DateTime()),
            array('relatedContact', $this->getMock('OroCRM\Bundle\ContactBundle\Entity\Contact')),
            array('relatedAccount', $this->getMock('OroCRM\Bundle\AccountBundle\Entity\Account'))
        );
    }
}
