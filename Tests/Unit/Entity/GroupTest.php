<?php

namespace Oro\Bundle\UserBundle\Tests\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\UserBundle\Entity\Group;
use Oro\Bundle\UserBundle\Entity\Role;

class GroupTest extends \PHPUnit_Framework_TestCase
{
    public function testGroup()
    {
        $group = $this->getGroup();
        $name  = 'Users';

        $this->assertEmpty($group->getId());
        $this->assertEmpty($group->getName());

        $group->setName($name);

        $this->assertEquals($name, $group->getName());
    }

    public function testRoles()
    {
        $group = $this->getGroup();
        $role  = new Role('ROLE_FOO');

        $this->assertCount(0, $group->getRoles());
        $this->assertNull($group->getRole($role));
        $this->assertFalse($group->hasRole($role));

        $group->addRole($role);

        $this->assertTrue($group->hasRole($role));
        $this->assertEquals($role, $group->getRole($role));

        $group->removeRole($role);

        $this->assertFalse($group->hasRole($role));

        $roles = array($role);

        $group->setRoles($roles);

        $this->assertEquals($roles, $group->getRoles()->toArray());

        $roles = new ArrayCollection(array($role));

        $group->setRoles($roles);

        $this->assertEquals($roles, $group->getRoles());
        $this->assertNotEmpty($group->getRoleLabelsAsString());
    }

    protected function setUp()
    {
        $this->group = new Group();
    }

    /**
     * @return Group
     */
    protected function getGroup()
    {
        return $this->group;
    }
}
