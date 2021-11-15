<?php

namespace Oro\Bundle\CaseBundle\Tests\Unit\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\CaseBundle\Entity\CaseComment;
use Oro\Bundle\CaseBundle\Entity\CaseEntity;
use Oro\Bundle\CaseBundle\Entity\CasePriority;
use Oro\Bundle\CaseBundle\Entity\CaseSource;
use Oro\Bundle\CaseBundle\Entity\CaseStatus;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;

class CaseEntityTest extends \PHPUnit\Framework\TestCase
{
    /** @var CaseEntity */
    private $case;

    protected function setUp(): void
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

    public function settersAndGettersDataProvider(): array
    {
        $source = $this->createMock(CaseSource::class);
        $status = $this->createMock(CaseStatus::class);
        $priority = $this->createMock(CasePriority::class);

        return [
            ['subject', 'Test subject'],
            ['description', 'Test Description'],
            ['resolution', 'Test Resolution'],
            ['assignedTo', $this->createMock(User::class)],
            ['owner', $this->createMock(User::class)],
            ['source', $source],
            ['status', $status],
            ['priority', $priority],
            ['createdAt', new \DateTime()],
            ['updatedAt', new \DateTime()],
            ['reportedAt', new \DateTime()],
            ['closedAt', new \DateTime()],
            ['relatedContact', $this->createMock(Contact::class)],
            ['relatedAccount', $this->createMock(Account::class)],
            ['organization', $this->createMock(Organization::class)]
        ];
    }

    public function testGetComments()
    {
        $this->assertInstanceOf(ArrayCollection::class, $this->case->getComments());

        $this->assertEquals(0, $this->case->getComments()->count());
    }

    public function testAddComment()
    {
        $comment = $this->createMock(CaseComment::class);
        $comment->expects($this->once())
            ->method('setCase')
            ->with($this->case);

        $this->assertEquals($this->case, $this->case->addComment($comment));

        $this->assertEquals($comment, $this->case->getComments()->get(0));
    }
}
