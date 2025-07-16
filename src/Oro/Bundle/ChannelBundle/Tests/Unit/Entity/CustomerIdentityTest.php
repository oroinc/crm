<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Entity;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Entity\CustomerIdentity;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;
use PHPUnit\Framework\TestCase;

class CustomerIdentityTest extends TestCase
{
    use EntityTestCaseTrait;

    public function testProperties(): void
    {
        $properties = [
            'id'          => ['id', 1],
            'name'        => ['name', 'Some name'],
            'owner'       => ['owner', $this->createMock(User::class)],
            'account'     => ['account', $this->createMock(Account::class)],
            'contact'     => ['contact', $this->createMock(Contact::class)],
            'dataChannel' => ['dataChannel', $this->createMock(Channel::class)],
            'createdAt'   => ['createdAt', new \DateTime()],
            'updatedAt'   => ['updatedAt', new \DateTime()],
        ];

        $entity = new CustomerIdentity();
        self::assertPropertyAccessors($entity, $properties);
    }

    public function testPrePersist(): void
    {
        $entity = new CustomerIdentity();
        $entity->prePersist();

        self::assertNotNull($entity->getCreatedAt());
        self::assertNotNull($entity->getUpdatedAt());
        self::assertEquals($entity->getCreatedAt(), $entity->getUpdatedAt());
        self::assertNotSame($entity->getCreatedAt(), $entity->getUpdatedAt());

        $existingCreatedAt = $entity->getCreatedAt();
        $existingUpdatedAt = $entity->getUpdatedAt();
        $entity->prePersist();
        self::assertSame($existingCreatedAt, $entity->getCreatedAt());
        self::assertNotSame($existingUpdatedAt, $entity->getUpdatedAt());
    }

    public function testPreUpdate(): void
    {
        $entity = new CustomerIdentity();
        $entity->preUpdate();

        self::assertNotNull($entity->getUpdatedAt());

        $existingUpdatedAt = $entity->getUpdatedAt();
        $entity->preUpdate();
        self::assertNotSame($existingUpdatedAt, $entity->getUpdatedAt());
    }
}
