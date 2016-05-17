<?php

namespace OroCRM\Bundle\SalesBundle\Tests\Unit\Autocomplete;

use OroCRM\Bundle\SalesBundle\Autocomplete\ForecastWidgetBusinessUnitSearchHandler;

class ForecastWidgetBusinessUnitSearchHandlerTest extends \PHPUnit_Framework_TestCase
{
    const TEST_ID_FIELD = 'id';
    const TEST_ENTITY_NAME = 'OroOrganizationBundle:BusinessUnit';
    const TEST_ENTITY_ALIAS = 'business_alias';

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $businessAclProvider;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $handler;

    public function setUp()
    {
        $this->businessAclProvider = $this
            ->getMockBuilder('Oro\Bundle\OrganizationBundle\Provider\BusinessUnitAclProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->handler = new ForecastWidgetBusinessUnitSearchHandler(
            self::TEST_ENTITY_NAME,
            [],
            $this->businessAclProvider,
            'OroCRMSalesBundle:Opportunity'
        );
    }

    public function testApplyBusinessUnitAcl()
    {
        $actualElements = [
            new SearchElement(1),
            new SearchElement(2),
            new SearchElement(3)
        ];
        $expectedIds = [1, 2];

        $query = $this
            ->getMockBuilder('Oro\Bundle\SearchBundle\Query\Query')
            ->setMethods(['getElements'])
            ->disableOriginalConstructor()
            ->getMock();

        $query->expects($this->once())
            ->method('getElements')
            ->will($this->returnValue($actualElements));

        $this->businessAclProvider
            ->expects($this->exactly(1))
            ->method('getBusinessUnitIds')
            ->will($this->returnValue($expectedIds));

        $indexer = $this->getMockBuilder('Oro\Bundle\SearchBundle\Engine\Indexer')
            ->setMethods(['query', 'select', 'simpleSearch'])
            ->disableOriginalConstructor()->getMock();

        $indexer->expects($this->once())
            ->method('simpleSearch')
            ->will($this->returnValue($query));

        $expr = $this->getMockBuilder('Doctrine\ORM\Query\Expr')
            ->setMethods(['in'])
            ->disableOriginalConstructor()
            ->getMock();

        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->setMethods(['getQuery', 'getResult', 'where', 'expr'])
            ->disableOriginalConstructor()
            ->getMock();

        $queryBuilder
            ->expects($this->exactly(1))
            ->method('expr')
            ->will($this->returnValue($expr));

        $queryBuilder->expects($this->any())
            ->method('getQuery')
            ->will($this->returnValue($queryBuilder));

        $queryBuilder->expects($this->any())
            ->method('getResult')
            ->will($this->returnValue([]));

        $entityRepository = $this
            ->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->setMethods(['createQueryBuilder'])
            ->disableOriginalConstructor()
            ->getMock();

        $entityRepository->expects($this->any())
            ->method('createQueryBuilder')
            ->will($this->returnValue($queryBuilder));

        $metadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()->getMock();
        $metadata->expects($this->once())->method('getSingleIdentifierFieldName')
            ->will($this->returnValue(self::TEST_ID_FIELD));

        $metadataFactory = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadataFactory')
            ->disableOriginalConstructor()->getMock();
        $metadataFactory->expects($this->once())
            ->method('getMetadataFor')->with(self::TEST_ENTITY_NAME)
            ->will($this->returnValue($metadata));

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()->getMock();
        $em->expects($this->once())->method('getRepository')
            ->will($this->returnValue($entityRepository));
        $em->expects($this->once())->method('getMetadataFactory')
            ->will($this->returnValue($metadataFactory));

        $managerRegistry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');
        $managerRegistry->expects($this->once())->method('getManagerForClass')->with(self::TEST_ENTITY_NAME)
            ->will($this->returnValue($em));

        $this->handler->initSearchIndexer($indexer, [self::TEST_ENTITY_NAME => ['alias' => self::TEST_ENTITY_ALIAS]]);
        $this->handler->initDoctrinePropertiesByManagerRegistry($managerRegistry);

        //the main filter check
        $expr
            ->expects($this->once())
            ->method('in')
            ->with('e.'.self::TEST_ID_FIELD, $expectedIds);

        $this->handler->search('query', 0, 10);
    }
}
