<?php

namespace OroCRM\Bundle\CaseBundle\Tests\Unit\Entity;

use OroCRM\Bundle\CaseBundle\Entity\CaseEntity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;

class CaseEntityTest extends \PHPUnit_Framework_TestCase
{
    const TEST_ID = 12;

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
}
