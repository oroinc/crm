<?php

namespace OroCRM\Bundle\MarketingListBundle\Tests\Unit\Provider;

use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Provider\MarketingListVirtualRelationProvider;

class MarketingListVirtualRelationProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $doctrineHelper;

    /**
     * @var MarketingListVirtualRelationProvider
     */
    protected $provider;

    protected function setUp()
    {
        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = new MarketingListVirtualRelationProvider($this->doctrineHelper);
    }

    /**
     * @dataProvider fieldDataProvider
     * @param string $className
     * @param string $fieldName
     * @param MarketingList $marketingList
     * @param bool $supported
     */
    public function testIsVirtualRelation($className, $fieldName, $marketingList, $supported)
    {
        $this->assertRepositoryCall($className, $marketingList);
        $this->assertEquals($supported, $this->provider->isVirtualRelation($className, $fieldName));
    }

    /**
     * @return array
     */
    public function fieldDataProvider()
    {
        $marketingList = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();

        return [
            'incorrect class incorrect field' => ['stdClass', 'test', null, false],
            'incorrect class correct field' => [
                'stdClass',
                MarketingListVirtualRelationProvider::RELATION_NAME,
                null,
                false
            ],
            'incorrect field' => ['stdClass', 'test', $marketingList, false],
            'correct' => ['stdClass', MarketingListVirtualRelationProvider::RELATION_NAME, $marketingList, true],
        ];
    }

    public function testGetVirtualRelationsNoRelations()
    {
        $className = 'stdClass';

        $this->assertRepositoryCall($className, null);
        $result = $this->provider->getVirtualRelations($className);

        $this->doctrineHelper->expects($this->never())
            ->method('getSingleEntityIdentifierFieldName');
        $this->assertEmpty($result);
    }

    public function testGetVirtualRelations()
    {
        $className = 'stdClass';
        $marketingList = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();

        $this->assertRepositoryCall($className, $marketingList);
        $this->doctrineHelper->expects($this->once())
            ->method('getSingleEntityIdentifierFieldName')
            ->with($className)
            ->will($this->returnValue('id'));

        $result = $this->provider->getVirtualRelations($className);
        $this->assertArrayHasKey(MarketingListVirtualRelationProvider::RELATION_NAME, $result);
    }

    /**
     * @return array
     */
    public function relationsDataProvider()
    {
        $marketingList = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();

        return [
            'incorrect class incorrect field' => ['stdClass', null, false],
            'correct' => ['stdClass', $marketingList, true],
        ];
    }

    /**
     * @dataProvider fieldDataProvider
     * @param string $className
     * @param string $fieldName
     * @param MarketingList $marketingList
     * @param bool $supported
     */
    public function tesGetVirtualRelationQueryUnsupportedClass($className, $fieldName, $marketingList, $supported)
    {
        $this->assertRepositoryCall($className, $marketingList);
        if ($supported) {
            $this->doctrineHelper->expects($this->once())
                ->method('getSingleEntityIdentifierFieldName')
                ->with($className)
                ->will($this->returnValue('id'));
        }

        $result = $this->provider->getVirtualRelationQuery($className, $fieldName);

        if ($supported) {
            $this->assertNotEmpty($result);
        } else {
            $this->assertNotEmpty($result);
        }
    }

    /**
     * @param string $className
     * @param object $marketingList
     */
    protected function assertRepositoryCall($className, $marketingList)
    {
        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(['getArrayResult'])
            ->getMockForAbstractClass();

        $results = [];
        if ($marketingList) {
            $results[] = ['entity' => $className];
        }

        $query->expects($this->once())
            ->method('getArrayResult')
            ->will($this->returnValue($results));

        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $queryBuilder->expects($this->once())
            ->method('select')
            ->with('ml.entity')
            ->will($this->returnSelf());
        $queryBuilder->expects($this->once())
            ->method('distinct')
            ->with(true)
            ->will($this->returnSelf());
        $queryBuilder->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));

        $repository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('ml')
            ->will($this->returnValue($queryBuilder));

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepository')
            ->with('OroCRMMarketingListBundle:MarketingList')
            ->will($this->returnValue($repository));
    }

    /**
     * @param string $selectFieldName
     * @param string $expected
     *
     * @dataProvider targetJoinAliasDataProvider
     */
    public function testGetTargetJoinAlias($selectFieldName, $expected)
    {
        $this->assertEquals(
            $expected,
            $this->provider->getTargetJoinAlias(null, null, $selectFieldName)
        );
    }

    /**
     * @return array
     */
    public function targetJoinAliasDataProvider()
    {
        return [
            [null, 'marketingList_virtual'],
            ['', 'marketingList_virtual'],
            ['field', 'marketingList_virtual'],
            ['marketingList', 'marketingList_virtual'],
            ['marketingListItem', 'marketingListItems'],
            ['marketingListItems', 'marketingListItems'],
        ];
    }
}
