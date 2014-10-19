<?php

namespace OroCRM\Bundle\CaseBundle\Tests\Unit\Entity;

use OroCRM\Bundle\CaseBundle\Entity\CaseEntity;

class CaseEntityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CaseEntity
     */
    protected $case;

    protected function setUp()
    {
        $this->case = new CaseEntity();
    }

    /**
     * @dataProvider settersAndGettersDataProvider
     */
    public function testSettersAndGetters($property, $value)
    {
        $method = 'set' . ucfirst($property);
        $result = $this->case->$method($value);

        $this->assertInstanceOf(get_class($this->case), $result);
        $this->assertEquals($value, $this->case->{'get' . $property}());
    }

    public function settersAndGettersDataProvider()
    {
        $source = $this->getMockBuilder('OroCRM\Bundle\CaseBundle\Entity\CaseSource')
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
            array('source', $source),
            array('status', $status),
            array('priority', $priority),
            array('createdAt', new \DateTime()),
            array('updatedAt', new \DateTime()),
            array('reportedAt', new \DateTime()),
            array('closedAt', new \DateTime()),
            array('relatedContact', $this->getMock('OroCRM\Bundle\ContactBundle\Entity\Contact')),
            array('relatedAccount', $this->getMock('OroCRM\Bundle\AccountBundle\Entity\Account')),
            array('organization', $this->getMock('Oro\Bundle\OrganizationBundle\Entity\Organization'))
        );
    }

    public function testGetComments()
    {
        $this->assertInstanceOf('Doctrine\Common\Collections\ArrayCollection', $this->case->getComments());

        $this->assertEquals(0, $this->case->getComments()->count());
    }

    public function testAddComment()
    {
        $comment = $this->getMock('OroCRM\Bundle\CaseBundle\Entity\CaseComment');
        $comment->expects($this->once())
            ->method('setCase')
            ->with($this->case);

        $this->assertEquals($this->case, $this->case->addComment($comment));

        $this->assertEquals($comment, $this->case->getComments()->get(0));
    }

    public function testGetPhoneNumber()
    {
        $case = new CaseEntity();
        $contact = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\Entity\Contact')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertNull($case->getPhoneNumber());

        $case->setRelatedContact($contact);
        $contact->expects($this->once())
            ->method('getPhoneNumber')
            ->will($this->returnValue('123-123'));
        $this->assertEquals('123-123', $case->getPhoneNumber());
    }

    public function testGetPhoneNumbers()
    {
        $case = new CaseEntity();
        $contact = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\Entity\Contact')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertSame([], $case->getPhoneNumbers());

        $case->setRelatedContact($contact);
        $contact->expects($this->once())
            ->method('getPhoneNumbers')
            ->will($this->returnValue(['123-123', '456-456']));
        $this->assertSame(['123-123', '456-456'], $case->getPhoneNumbers());
    }
}
