<?php

namespace OroCRM\Bundle\CaseBundle\Tests\Unit\Entity;

use OroCRM\Bundle\CaseBundle\Entity\CaseComment;

class CaseCommentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CaseComment
     */
    protected $comment;

    protected function setUp()
    {
        $this->comment = new CaseComment();
    }

    public function testId()
    {
        $this->assertNull($this->comment->getId());

        $value = 100;

        $reflectionProperty = new \ReflectionProperty(get_class($this->comment), 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->comment, $value);

        $this->assertEquals($value, $this->comment->getId());
    }

    public function testMessage()
    {
        $this->assertNull($this->comment->getMessage());

        $value = 'Test';

        $this->assertEquals($this->comment, $this->comment->setMessage($value));
        $this->assertEquals($value, $this->comment->getMessage());
    }

    public function testPublic()
    {
        $this->assertFalse($this->comment->isPublic());

        $this->assertEquals($this->comment, $this->comment->setPublic(true));
        $this->assertTrue($this->comment->isPublic());
    }

    public function testContact()
    {
        $this->assertNull($this->comment->getContact());

        $value = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\Entity\Contact')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertEquals($this->comment, $this->comment->setContact($value));
        $this->assertEquals($value, $this->comment->getContact());
    }

    public function testCase()
    {
        $this->assertNull($this->comment->getCase());

        $value = $this->getMockBuilder('OroCRM\Bundle\CaseBundle\Entity\CaseEntity')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertEquals($this->comment, $this->comment->setCase($value));
        $this->assertEquals($value, $this->comment->getCase());
    }

    public function testUpdatedBy()
    {
        $this->assertNull($this->comment->getUpdatedBy());

        $value = $this->getMockBuilder('Oro\Bundle\UserBundle\Entity\User')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertEquals($this->comment, $this->comment->setUpdatedBy($value));
        $this->assertEquals($value, $this->comment->getUpdatedBy());
    }

    public function testOwner()
    {
        $this->assertNull($this->comment->getOwner());

        $value = $this->getMockBuilder('Oro\Bundle\UserBundle\Entity\User')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertEquals($this->comment, $this->comment->setOwner($value));
        $this->assertEquals($value, $this->comment->getOwner());
    }

    public function testCreatedAt()
    {
        $this->assertNull($this->comment->getCreatedAt());

        $value = new \DateTime();

        $this->assertEquals($this->comment, $this->comment->setCreatedAt($value));
        $this->assertEquals($value, $this->comment->getCreatedAt());
    }

    public function testUpdatedAt()
    {
        $this->assertNull($this->comment->getUpdatedAt());

        $value = new \DateTime();

        $this->assertEquals($this->comment, $this->comment->setUpdatedAt($value));
        $this->assertEquals($value, $this->comment->getUpdatedAt());
    }

    public function testPrePersist()
    {
        $this->assertNull($this->comment->getCreatedAt());

        $this->comment->prePersist();

        $this->assertInstanceOf('DateTime', $this->comment->getCreatedAt());
        $this->assertLessThan(3, $this->comment->getCreatedAt()->diff(new \DateTime())->s);
    }

    public function testPreUpdate()
    {
        $this->assertNull($this->comment->getUpdatedAt());

        $this->comment->preUpdate();

        $this->assertInstanceOf('DateTime', $this->comment->getUpdatedAt());
        $this->assertLessThan(3, $this->comment->getUpdatedAt()->diff(new \DateTime())->s);
    }
}
