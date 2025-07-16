<?php

namespace Oro\Bundle\CaseBundle\Tests\Unit\Entity;

use Oro\Bundle\CaseBundle\Entity\CaseComment;
use Oro\Bundle\CaseBundle\Entity\CaseEntity;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\Testing\ReflectionUtil;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CaseCommentTest extends TestCase
{
    private CaseComment $comment;

    #[\Override]
    protected function setUp(): void
    {
        $this->comment = new CaseComment();
    }

    public function testId(): void
    {
        $this->assertNull($this->comment->getId());

        $value = 100;
        ReflectionUtil::setId($this->comment, $value);
        $this->assertEquals($value, $this->comment->getId());
    }

    public function testMessage(): void
    {
        $this->assertNull($this->comment->getMessage());

        $value = 'Test';

        $this->assertEquals($this->comment, $this->comment->setMessage($value));
        $this->assertEquals($value, $this->comment->getMessage());
    }

    public function testPublic(): void
    {
        $this->assertFalse($this->comment->isPublic());

        $this->assertEquals($this->comment, $this->comment->setPublic(true));
        $this->assertTrue($this->comment->isPublic());
    }

    public function testContact(): void
    {
        $this->assertNull($this->comment->getContact());

        $value = $this->createMock(Contact::class);

        $this->assertEquals($this->comment, $this->comment->setContact($value));
        $this->assertEquals($value, $this->comment->getContact());
    }

    public function testCase(): void
    {
        $this->assertNull($this->comment->getCase());

        $value = $this->createMock(CaseEntity::class);

        $this->assertEquals($this->comment, $this->comment->setCase($value));
        $this->assertEquals($value, $this->comment->getCase());
    }

    public function testUpdatedBy(): void
    {
        $this->assertNull($this->comment->getUpdatedBy());

        $value = $this->createMock(User::class);

        $this->assertEquals($this->comment, $this->comment->setUpdatedBy($value));
        $this->assertEquals($value, $this->comment->getUpdatedBy());
    }

    public function testOwner(): void
    {
        $this->assertNull($this->comment->getOwner());

        $value = $this->createMock(User::class);

        $this->assertEquals($this->comment, $this->comment->setOwner($value));
        $this->assertEquals($value, $this->comment->getOwner());
    }

    public function testCreatedAt(): void
    {
        $this->assertNull($this->comment->getCreatedAt());

        $value = new \DateTime();

        $this->assertEquals($this->comment, $this->comment->setCreatedAt($value));
        $this->assertEquals($value, $this->comment->getCreatedAt());
    }

    public function testUpdatedAt(): void
    {
        $this->assertNull($this->comment->getUpdatedAt());

        $value = new \DateTime();

        $this->assertEquals($this->comment, $this->comment->setUpdatedAt($value));
        $this->assertEquals($value, $this->comment->getUpdatedAt());
    }

    public function testPrePersist(): void
    {
        $this->assertNull($this->comment->getCreatedAt());

        $this->comment->prePersist();

        $this->assertInstanceOf('DateTime', $this->comment->getCreatedAt());
        $this->assertLessThan(3, $this->comment->getCreatedAt()->diff(new \DateTime())->s);
    }

    public function testPreUpdate(): void
    {
        $this->assertNull($this->comment->getUpdatedAt());

        $this->comment->preUpdate();

        $this->assertInstanceOf('DateTime', $this->comment->getUpdatedAt());
        $this->assertLessThan(3, $this->comment->getUpdatedAt()->diff(new \DateTime())->s);
    }
}
