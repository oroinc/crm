<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Entity;

use Oro\Bundle\ContactBundle\Entity\Group;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use PHPUnit\Framework\TestCase;

class GroupTest extends TestCase
{
    private Group $group;

    #[\Override]
    protected function setUp(): void
    {
        $this->group = new Group();
    }

    public function testConstructor(): void
    {
        $this->assertNull($this->group->getLabel());

        $group = new Group('Label');
        $this->assertEquals('Label', $group->getLabel());
    }

    public function testLabel(): void
    {
        $this->assertNull($this->group->getLabel());
        $this->group->setLabel('Label');
        $this->assertEquals('Label', $this->group->getLabel());
        $this->assertEquals('Label', $this->group->__toString());
    }

    public function testOwners(): void
    {
        $entity = new Group();
        $user = new User();

        $this->assertEmpty($entity->getOwner());

        $entity->setOwner($user);

        $this->assertEquals($user, $entity->getOwner());
    }

    public function testOrganization(): void
    {
        $entity = new Group();
        $organization = new Organization();

        $this->assertNull($entity->getOrganization());
        $entity->setOrganization($organization);
        $this->assertSame($organization, $entity->getOrganization());
    }
}
