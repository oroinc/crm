<?php

namespace Oro\Bundle\UserBundle\Tests\Entity;

use Oro\Bundle\UserBundle\Entity\Group;
use Oro\Bundle\UserBundle\Entity\Role;

class GroupTest extends \PHPUnit_Framework_TestCase
{
    public function testGroup()
    {
        $group = $this->getGroup();
        $name  = 'Users';

        $this->assertEmpty($group->getName());

        $group->setName($name);

        $this->assertEquals($name, $group->getName());
    }

    public function testRoles()
    {
        $group = $this->getGroup();
        $role  = new Role('ROLE_FOO');

        $this->assertEmpty($group->getRoles());
        $this->assertCount(0, $group->getRolesCollection());
        $this->assertNull($group->getRole($role));
        $this->assertFalse($group->hasRole($role));

        $group->addRole($role);

        $this->assertTrue($group->hasRole($role));
        $this->assertEquals($role, $group->getRole($role));

        $group->removeRole($role);

        $this->assertFalse($group->hasRole($role));

        $roles = array($role);

        $group->setRoles($roles);

        $this->assertEquals($roles, $group->getRoles());
    }

    public function testRoleException()
    {
        $group = $this->getGroup();
        $role  = new \stdClass();

        $this->setExpectedException('InvalidArgumentException');

        $group->addRole($role);
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
