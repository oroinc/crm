<?php
namespace Oro\Bundle\UserBundle\Tests\Entity;

use Oro\Bundle\UserBundle\Entity\Acl;

class AclTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Oro\Bundle\UserBundle\Entity\Acl
     */
    private $acl;

    public function setUp()
    {
        $this->acl = new Acl();
    }

    public function testName()
    {
        $this->assertNull($this->acl->getName());
        $this->acl->setName('test_acl');
        $this->assertEquals('test_acl', $this->acl->getName());
    }

    public function testDescription()
    {
        $this->assertNull($this->acl->getDescription());
        $this->acl->setDescription('test_description');
        $this->assertEquals('test_description', $this->acl->getDescription());
    }
}
