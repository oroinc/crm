<?php
namespace Oro\Bundle\UserBundle\Tests\Unit\Acl;

use Oro\Bundle\UserBundle\Acl\Manager;
use Oro\Bundle\UserBundle\Entity\Acl;
use Oro\Bundle\UserBundle\Entity\Role;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Oro\Bundle\UserBundle\Acl\Manager
     */
    private $manager;

    private $user;

    private $repository;

    private $om;

    private $cache;

    private $testRole;

    private $aclObject;

    public function setUp()
    {
        if (!interface_exists('Doctrine\Common\Persistence\ObjectManager')) {
            $this->markTestSkipped('Doctrine Common has to be installed for this test to run.');
        }

        $this->user = $this->getMock('Oro\Bundle\UserBundle\Entity\User');
        $this->om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->repository = $this->getMock(
            'Doctrine\Common\Persistence\ObjectRepository',
            array('find', 'findAll', 'findBy', 'findOneBy', 'getClassName', 'getAllowedAclResourcesForUserRoles',
            'getFullNodeWithRoles')
        );

        $this->repository->expects($this->any())
            ->method('getAllowedAclResourcesForUserRoles')
            ->will($this->returnValue(array('test')));

        $this->user->expects($this->any())
            ->method('getRoles')
            ->will($this->returnValue(array()));

        $this->om->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($this->repository));

        $reader = $this->getMock(
            'Oro\Bundle\UserBundle\Acl\ResourceReader\Reader',
            array(),
            array(),
            '',
            false
        );

        $sqlExecMock = $this->getMock('Doctrine\ORM\Query\Exec\AbstractSqlExecutor', array('execute'));
        $sqlExecMock->expects($this->any())
            ->method('execute')
            ->will($this->returnValue( 10 ));
        $parserResultMock = $this->getMock('Doctrine\ORM\Query\ParserResult');
        $parserResultMock->expects($this->any())
            ->method('getSqlExecutor')
            ->will($this->returnValue($sqlExecMock));
        $this->cache = $this->getMock('Doctrine\Common\Cache\CacheProvider',
            array('doFetch', 'doContains', 'doSave', 'doDelete', 'doFlush', 'doGetStats', 'fetch', 'save'));

        $this->manager = new Manager($this->om, $reader, $this->cache);

        $this->testRole = new Role();
        $this->testRole->setRole('TEST_ROLE')
            ->setLabel('test role');

        $this->aclObject = new Acl();
        $this->aclObject->setDescription('test_acl')
            ->setId('test_acl')
            ->setName('test_acl')
            ->addAccessRole($this->testRole);
    }

    public function testGetAclForUser()
    {
        $result= $this->manager->getAclForUser($this->user);
        $this->assertEquals(array('test'), $result);
    }

    public function testGetCachedAcl()
    {

        $testAclName = 'test_acl';

        $this->cache->expects($this->once())
            ->method('fetch')
            ->will($this->returnValue(false));

        $this->repository->expects($this->once())
            ->method('find')
            ->will($this->returnValue($this->aclObject));

        $this->repository->expects($this->once())
            ->method('getFullNodeWithRoles')
            ->with($this->equalTo($this->aclObject))
            ->will($this->returnValue(array($this->aclObject)));

        $this->assertEquals(
            array('ROLE_TEST_ROLE'),
            $this->manager->getCachedAcl($testAclName)
        );
    }
}
