<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\ImportExport\Serializer\Normalizer;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;

use OroCRM\Bundle\ContactBundle\ImportExport\Provider\MaxDataProvider;

class MaxDataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MaxDataProvider
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

        $this->provider = new MaxDataProvider($this->registry);
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
