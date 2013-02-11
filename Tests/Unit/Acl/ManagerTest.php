<?php
namespace Oro\Bundle\UserBundle\Tests\Unit\Acl;

use Oro\Bundle\UserBundle\Acl\Manager;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    private $manager;

    private $user;

    public function setUp()
    {
        if (!interface_exists('Doctrine\Common\Persistence\ObjectManager')) {
            $this->markTestSkipped('Doctrine Common has to be installed for this test to run.');
        }

        $om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');

        $om->expects($this->any())
            ->method('getRepository')
            ->with($this->equalTo('OroUserBundle:Acl'))
            ->will($this->returnValue($repository));

        $repository->expects($this->any())
            ->method('getAllowedAclResourcesForUserRoles')
            ->with($this->equalTo('OroUserBundle:Acl'))
            ->will($this->returnValue(array('test')));

        $reader = $this->getMock('Oro\Bundle\UserBundle\Acl\ResourceReader\Reader');

        $cache = $this->getMock('Doctrine\Common\Cache\CacheProvider');
        $cache->expects($this->any())
            ->method('setNamespace')
            ->with($this->equalTo('oro_user.cache'));

        $this->user = $this->getMock('Oro\Bundle\UserBundle\Entity\User');

        $this->manager = new Manager($om, $reader, $cache);
    }

    public function testGetAclForUser()
    {
        $result= $this->manager->getAclForUser($this->user);
        $this->assertEquals(array(), $result);
    }
}
