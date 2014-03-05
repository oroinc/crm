<?php

namespace OroCRM\Bundle\TaskBundle\Tests\Unit\Entity;

use OroCRM\Bundle\TaskBundle\Entity\Task;

class TaskTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        new Task();
    }

    public function testPrePersistShouldSetCreatedAt()
    {
        $entity = new Task();
        $entity->prePersist();

        $this->assertEquals($entity->getCreatedAt()->format("m/d/Y"), date("m/d/Y"));
    }

    public function testPreUpdateShouldSetUpdatedAt()
    {
        $entity = new Task();
        $entity->preUpdate();

        $this->assertEquals($entity->getUpdatedAt()->format("m/d/Y H:i"), date("m/d/Y H:i"));
    }

    public function testGetSetStatusShouldChangeStatus()
    {
        $entity = new Task();
        $status = $this->getMock('Oro\Bundle\WorkflowBundle\Entity\WorkflowStep');

        $this->assertNull($entity->getStatus());

        $entity->setStatus($status);

        $this->assertEquals($status, $entity->getStatus());
    }

    public function testGetStatusNameShouldReturnCorrectStatusName()
    {
        $entity = new Task();

        $this->assertNull($entity->getStatusName());

        $status = $this->getMock('Oro\Bundle\WorkflowBundle\Entity\WorkflowStep');
        $expected = 'sample status';
        $status->expects($this->once())->method('getName')->will($this->returnValue($expected));
        $entity->setStatus($status);

        $this->assertEquals($entity->getStatusName(), $expected);
    }

    public function testSetRelatedContactShouldChangeRelatedContact()
    {
        $entity = new Task();

        $this->assertNull($entity->getRelatedContact());

        $contact = $this->getMock('OroCRM\Bundle\ContactBundle\Entity\Contact');
        $entity->setRelatedContact($contact);

        $this->assertEquals($contact, $entity->getRelatedContact());
    }

    public function testGetRelatedContactIdShouldReturnCorrectValues()
    {
        $entity = new Task();

        $this->assertNull($entity->getRelatedAccountId());

        $contact = $this->getMock('OroCRM\Bundle\ContactBundle\Entity\Contact');
        $expected = 42;
        $contact->expects($this->once())->method('getId')->will($this->returnValue($expected));
        $entity->setRelatedContact($contact);

        $this->assertEquals($expected, $entity->getRelatedContactId());
    }

    public function testSetRelatedAccountShouldChangeRelatedAccount()
    {
        $entity = new Task();

        $this->assertNull($entity->getRelatedAccount());

        $account = $this->getMock('OroCRM\Bundle\AccountBundle\Entity\Account');
        $entity->setRelatedAccount($account);

        $this->assertEquals($account, $entity->getRelatedAccount());
    }

    public function testGetRelatedAccountIdShouldReturnCorrectValues()
    {
        $entity = new Task();

        $this->assertNull($entity->getRelatedAccountId());

        $account = $this->getMock('OroCRM\Bundle\AccountBundle\Entity\Account');
        $expected = 42;
        $account->expects($this->once())->method('getId')->will($this->returnValue($expected));
        $entity->setRelatedAccount($account);

        $this->assertEquals($expected, $entity->getRelatedAccountId());
    }

    public function testSetAssignedToShouldChangeAssignedTo()
    {
        $entity = new Task();

        $this->assertNull($entity->getAssignedTo());

        $user = $this->getMock('Oro\Bundle\UserBundle\Entity\User');
        $entity->setAssignedTo($user);

        $this->assertEquals($user, $entity->getAssignedTo());
    }

    public function testGetAssignedToIdShouldReturnCorrectValues()
    {
        $entity = new Task();

        $this->assertNull($entity->getAssigneeToId());

        $user = $this->getMock('Oro\Bundle\UserBundle\Entity\User');
        $expected = 42;
        $user->expects($this->once())->method('getId')->will($this->returnValue($expected));
        $entity->setAssignedTo($user);

        $this->assertEquals($expected, $entity->getAssigneeToId());
    }

    public function testSetOwnerShouldChangeOwner()
    {
        $entity = new Task();

        $this->assertNull($entity->getOwner());

        $user = $this->getMock('Oro\Bundle\UserBundle\Entity\User');
        $entity->setOwner($user);

        $this->assertSame($user, $entity->getOwner());
    }

    public function testGetOwnerIdShouldReturnCorrectValues()
    {
        $entity = new Task();

        $this->assertNull($entity->getOwnerId());

        $user = $this->getMock('Oro\Bundle\UserBundle\Entity\User');
        $expected = 42;
        $user->expects($this->once())->method('getId')->will($this->returnValue($expected));
        $entity->setOwner($user);

        $this->assertEquals($expected, $entity->getOwnerId());
    }

    /**
     * @dataProvider provider
     * @param string $property
     * @param mixed  $value
     */
    public function testSettersAndGetters($property, $value)
    {
        $obj = new Task();

        call_user_func_array(array($obj, 'set' . ucfirst($property)), array($value));
        $this->assertEquals($value, call_user_func_array(array($obj, 'get' . ucfirst($property)), array()));
    }

    public function provider()
    {
        $testTaskPriority = $this->getMockBuilder('OroCRM\Bundle\TaskBundle\Entity\TaskPriority')
            ->disableOriginalConstructor()
            ->getMock();
        $testTaskPriority->expects($this->once())->method('getName')->will($this->returnValue('low'));
        $testTaskPriority->expects($this->once())->method('getLabel')->will($this->returnValue('Low label'));
        return array(
            array('id', 42),
            array('subject', 'Test subject'),
            array('description', 'Test Description'),
            array('taskPriority', $testTaskPriority),
            array('dueDate', new \DateTime()),
            array('createdAt', new \DateTime()),
            array('updatedAt', new \DateTime())
        );
    }
}
