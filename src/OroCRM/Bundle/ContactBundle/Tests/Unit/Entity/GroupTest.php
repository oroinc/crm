<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use OroCRM\Bundle\ContactBundle\Entity\Group;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\OrganizationBundle\Entity\BusinessUnit;
use Oro\Bundle\OrganizationBundle\Entity\Organization;

class GroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Group
     */
    protected $group;

    protected function setUp()
    {
        $this->group = new Group();
    }

    public function testConstructor()
    {
        $this->assertNull($this->group->getLabel());

        $group = new Group('Label');
        $this->assertEquals('Label', $group->getLabel());
    }

    public function testLabel()
    {
        $this->assertNull($this->group->getLabel());
        $this->group->setLabel('Label');
        $this->assertEquals('Label', $this->group->getLabel());
        $this->assertEquals('Label', $this->group->__toString());
    }

    public function testOwners()
    {
        $entity = new Group();
        $user = new User();

        $this->assertEmpty($entity->getOwner());

        $entity->setOwner($user);

        $this->assertEquals($user, $entity->getOwner());
    }

    public function testOrganization()
    {
        $entity         = new Group();
        $organization   = new Organization();

        $this->assertNull($entity->getOrganization());
        $entity->setOrganization($organization);
        $this->assertSame($organization, $entity->getOrganization());
    }
}
