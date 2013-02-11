<?php
namespace Oro\Bundle\UserBundle\Tests\Unit\Acl;

use Oro\Bundle\UserBundle\Acl\Manager;

class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Oro\Bundle\UserBundle\Acl\Manager
     */
    private $manager;

    private $user;

    private $repository;

    private $om;

    public function setUp()
    {
        if (!interface_exists('Doctrine\Common\Persistence\ObjectManager')) {
            $this->markTestSkipped('Doctrine Common has to be installed for this test to run.');
        }

        $this->user = $this->getMock('Oro\Bundle\UserBundle\Entity\User');
        $this->om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');

        $this->user->expects($this->any())
            ->method('getRoles')
            ->will($this->returnValue(array()));

        $this->repository->expects($this->any())
            ->method('getAllowedAclResourcesForUserRoles')
            ->will($this->returnValue(array('test')));

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
        $sqlExecMock->expects($this->once())
            ->method('execute')
            ->will($this->returnValue( 10 ));
        $parserResultMock = $this->getMock('Doctrine\ORM\Query\ParserResult');
        $parserResultMock->expects($this->once())
            ->method('getSqlExecutor')
            ->will($this->returnValue($sqlExecMock));
        $cache = $this->getMock('Doctrine\Common\Cache\CacheProvider',
            array('doFetch', 'doContains', 'doSave', 'doDelete', 'doFlush', 'doGetStats'));
        $cache->expects($this->at(0))->method('doFetch')->will($this->returnValue(1));
        $cache->expects($this->at(1))
            ->method('doFetch')
            ->with($this->isType('string'))
            ->will($this->returnValue($parserResultMock));
        $cache->expects($this->never())
            ->method('doSave');
        $cache->expects($this->any())
            ->method('setNamespace')
            ->with($this->equalTo('oro_user.cache'));

        $this->manager = new Manager($this->om, $reader, $cache);
    }

    public function testGetAclForUser()
    {
        $result= $this->manager->getAclForUser($this->user);
        $this->assertEquals(array(), $result);
    }
}
