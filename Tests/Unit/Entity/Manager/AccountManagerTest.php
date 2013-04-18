<?php

namespace Oro\Bundle\AccountBundle\Tests\Unit\Entity\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Oro\Bundle\AccountBundle\Entity\Manager\AccountManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\AccountBundle\Entity\Account;

class AccountManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AccountManager
     */
    protected $manager;

    /**
     * @var string
     */
    protected $class = 'Oro\Bundle\AccountBundle\Entity\Account';

    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * @var FlexibleManager
     */
    protected $flexManager;

    protected function setUp()
    {
        $this->om = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $classMetadata = $this->getMock('\Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $classMetadata->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($this->class));
        $this->om->expects($this->once())
            ->method('getClassMetadata')
            ->with($this->class)
            ->will($this->returnValue($classMetadata));
        $this->flexManager = $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->manager = new AccountManager($this->class, $this->om, $this->flexManager);
    }

    public function testGetClass()
    {
        $this->assertEquals($this->class, $this->manager->getClass());
    }

    public function testSupportsClass()
    {
        $this->assertTrue($this->manager->supportsClass($this->class));
    }

    public function testSupportsClassNegative()
    {
        $this->assertFalse($this->manager->supportsClass('TestClass'));
    }

    public function testCreateAccount()
    {
        $this->assertInstanceOf($this->class, $this->manager->createAccount());
    }

    /**
     * @dataProvider doFlushDataProvider
     * @param bool $flush
     */
    public function testUpdateAccount($flush)
    {
        /** @var Account $account */
        $account = $this->getMock('Oro\Bundle\AccountBundle\Entity\Account');
        $this->om->expects($this->once())
            ->method('persist')
            ->with($account);
        $this->om->expects($this->exactly((int)$flush))
            ->method('flush');
        $this->manager->updateAccount($account, $flush);
    }

    /**
     * @return array
     */
    public function doFlushDataProvider()
    {
        return array(
            array(true),
            array(false)
        );
    }

    public function testDeleteAccount()
    {
        /** @var Account $account */
        $account = $this->getMock('Oro\Bundle\AccountBundle\Entity\Account');
        $this->om->expects($this->once())
            ->method('remove')
            ->with($account);
        $this->om->expects($this->once())
            ->method('flush');
        $this->manager->deleteAccount($account);
    }

    public function testFindAccountBy()
    {
        $criteria = array('id' => 1);
        $result = new \stdClass();
        /** @var ObjectRepository $repository */
        $repository = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->once())
            ->method('findOneBy')
            ->with($criteria)
            ->will($this->returnValue($result));
        $this->om->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($repository));
        $this->assertEquals($result, $this->manager->findAccountBy($criteria));
    }

    public function testFindAccounts()
    {
        $result = array();
        /** @var ObjectRepository $repository */
        $repository = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->once())
            ->method('findAll')
            ->will($this->returnValue($result));
        $this->om->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($repository));
        $this->assertEquals($result, $this->manager->findAccounts());
    }

    public function testReloadAccount()
    {
        /** @var Account $account */
        $account = $this->getMock('Oro\Bundle\AccountBundle\Entity\Account');
        $this->om->expects($this->once())
            ->method('refresh')
            ->with($account);
        $this->manager->reloadAccount($account);
    }

    public function testGetListQuery()
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $queryBuilder->expects($this->once())
            ->method('select')
            ->with('a')
            ->will($this->returnSelf());
        $queryBuilder->expects($this->once())
            ->method('from')
            ->with('OroAccountBundle:Account', 'a')
            ->will($this->returnSelf());
        $queryBuilder->expects($this->once())
            ->method('orderBy')
            ->with('a.id', 'ASC')
            ->will($this->returnSelf());
        $this->om->expects($this->once())
            ->method('createQueryBuilder')
            ->will($this->returnValue($queryBuilder));
        $this->assertEquals($queryBuilder, $this->manager->getListQuery());
    }

    public function testCall()
    {
        $result = new \stdClass();
        $this->flexManager->expects($this->once())
            ->method('createFlexible')
            ->will($this->returnValue($result));
        $this->assertEquals($result, $this->manager->createFlexible());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Unknown method "asdf"
     */
    public function testCallException()
    {
        $result = new \stdClass();
        $this->flexManager->expects($this->never())
            ->method('asdf');
        $this->assertEquals($result, $this->manager->asdf());
    }
}
