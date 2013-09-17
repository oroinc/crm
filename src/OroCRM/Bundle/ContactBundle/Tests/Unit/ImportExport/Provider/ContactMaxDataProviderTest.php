<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\ImportExport\Provider;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;

use OroCRM\Bundle\ContactBundle\ImportExport\Provider\ContactMaxDataProvider;

class ContactMaxDataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContactMaxDataProvider
     */
    protected $provider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $registry;

    protected function setUp()
    {
        $this->registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->provider = new ContactMaxDataProvider($this->registry);
    }

    protected function tearDown()
    {
        unset($this->registry);
        unset($this->provider);
    }

    public function testSetQueryBuilder()
    {
        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('resetDQLParts'))
            ->getMock();
        $queryBuilder->expects($this->once())
            ->method('resetDQLParts')
            ->with(array('groupBy', 'having', 'orderBy'));

        $this->assertAttributeEquals(null, 'queryBuilderPrototype', $this->provider);
        $this->provider->setQueryBuilder($queryBuilder);
        $this->assertAttributeInstanceOf('Doctrine\ORM\QueryBuilder', 'queryBuilderPrototype', $this->provider);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Max data query builder must have root alias
     */
    public function testGetRootAliasIsNotDefined()
    {
        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('getRootAliases'))
            ->getMock();
        $queryBuilder->expects($this->once())
            ->method('getRootAliases')
            ->will($this->returnValue(array()));

        $this->provider->setQueryBuilder($queryBuilder);
        $this->provider->getMaxAccountsCount();
    }

    /**
     * @return array
     */
    public function getMaxEntitiesDataProvider()
    {
        return array(
            'accounts count' => array(
                'method' => 'getMaxAccountsCount',
            ),
            'addresses count' => array(
                'method' => 'getMaxAddressesCount',
            ),
            'emails count' => array(
                'method' => 'getMaxEmailsCount',
            ),
            'phones count' => array(
                'method' => 'getMaxPhonesCount',
            ),
            'groups count' => array(
                'method' => 'getMaxGroupsCount',
            ),
        );
    }

    /**
     * Important: this test does not check generated DQL request, it's can't be done because of cloning
     *
     * @param string $method
     * @dataProvider getMaxEntitiesDataProvider
     */
    public function testGetMaxEntitiesCount($method)
    {
        $entityName  = 'OroCRMContactBundle:Contact';
        $entityAlias = 'contact';
        $expectedCount = 42;

        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('getOneOrNullResult'))
            ->getMockForAbstractClass();
        $query->expects($this->once())
            ->method('getOneOrNullResult')
            ->with(Query::HYDRATE_ARRAY)
            ->will($this->returnValue(array('maxCount' => $expectedCount)));

        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(array('getQuery'))
            ->getMock();
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder->from($entityName, $entityAlias);

        $repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->setMethods(array('createQueryBuilder'))
            ->getMock();
        $repository->expects($this->once())
            ->method('createQueryBuilder')
            ->with($entityAlias)
            ->will($this->returnValue($queryBuilder));

        $this->registry->expects($this->once())
            ->method('getRepository')
            ->with($entityName)
            ->will($this->returnValue($repository));

        $this->assertEquals($expectedCount, $this->provider->$method());
        $this->assertAttributeEquals($queryBuilder, 'queryBuilderPrototype', $this->provider);
    }
}
