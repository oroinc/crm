<?php
namespace Oro\Bundle\UserBundle\Tests\Entity;

use Oro\Bundle\UserBundle\Entity\Acl;
use Oro\Bundle\UserBundle\Entity\RoleAcl;
use Oro\Bundle\UserBundle\Entity\Role;

class AclTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Oro\Bundle\UserBundle\Entity\Acl
     */
    private $acl;

    /**
     * @var \Oro\Bundle\UserBundle\Entity\Role
     */
    private $role;

    /**
     * @var \Oro\Bundle\UserBundle\Entity\RoleAcl
     */
    private $roleAcl;

    public function setUp()
    {
        $this->acl = new Acl();
        $this->role = new Role();
        $this->roleAcl = new RoleAcl();
    }

    public function testRole()
    {
        $this->assertNull($this->roleAcl->getRole());
        $this->role->setLabel('test');
        $this->roleAcl->setRole($this->role);
        $this->assertEquals('test', $this->roleAcl->getRole()->getLabel());
    }

    public function testAclResource()
    {
        $this->assertNull($this->roleAcl->getAclResource());
        $this->acl->setName('test_acl');
        $this->roleAcl->setAclResource($this->acl);
        $this->assertEquals('test_description', $this->roleAcl->getAclResource());
    }

    public function testAccess()
    {
        $this->assertNull($this->roleAcl->getAccess());
        $this->roleAcl->setAccess(true);
        $this->assertEquals(true, $this->roleAcl->getAccess());
    }
}
