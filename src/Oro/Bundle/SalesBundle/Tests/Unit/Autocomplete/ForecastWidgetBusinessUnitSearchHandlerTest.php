<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Autocomplete;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\OrganizationBundle\Provider\BusinessUnitAclProvider;
use Oro\Bundle\SalesBundle\Autocomplete\ForecastWidgetBusinessUnitSearchHandler;
use Oro\Bundle\SearchBundle\Engine\Indexer;
use Oro\Bundle\SearchBundle\Query\Query;

class ForecastWidgetBusinessUnitSearchHandlerTest extends \PHPUnit\Framework\TestCase
{
    const TEST_ID_FIELD = 'id';
    const TEST_ENTITY_NAME = 'OroOrganizationBundle:BusinessUnit';
    const TEST_ENTITY_ALIAS = 'business_alias';

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $businessAclProvider;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $handler;

    public function setUp()
    {
        $this->businessAclProvider = $this->createMock(BusinessUnitAclProvider::class);

        $this->handler = new ForecastWidgetBusinessUnitSearchHandler(
            self::TEST_ENTITY_NAME,
            [],
            $this->businessAclProvider,
            'OroSalesBundle:Opportunity'
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
            ->getMockBuilder(Query::class)
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

        $indexer = $this->getMockBuilder(Indexer::class)
            ->setMethods(['query', 'select', 'simpleSearch'])
            ->disableOriginalConstructor()->getMock();

        $indexer->expects($this->once())
            ->method('simpleSearch')
            ->will($this->returnValue($query));

        $expr = $this->getMockBuilder(Expr::class)
            ->setMethods(['in'])
            ->disableOriginalConstructor()
            ->getMock();

        $queryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->setMethods(['getQuery', 'getResult', 'where', 'expr', 'setParameter'])
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
        $queryBuilder->expects($this->any())
            ->method('setParameter')
            ->with('entityIds', $expectedIds)
            ->willReturnSelf();

        $entityRepository = $this->getMockBuilder(EntityRepository::class)
            ->setMethods(['createQueryBuilder'])
            ->disableOriginalConstructor()
            ->getMock();

        $entityRepository->expects($this->any())
            ->method('createQueryBuilder')
            ->will($this->returnValue($queryBuilder));

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects($this->once())->method('getSingleIdentifierFieldName')
            ->will($this->returnValue(self::TEST_ID_FIELD));

        $metadataFactory = $this->createMock(ClassMetadataFactory::class);
        $metadataFactory->expects($this->once())
            ->method('getMetadataFor')->with(self::TEST_ENTITY_NAME)
            ->will($this->returnValue($metadata));

        $em = $this->createMock('Doctrine\ORM\EntityManager');
        $em->expects($this->once())->method('getRepository')
            ->will($this->returnValue($entityRepository));
        $em->expects($this->once())->method('getMetadataFactory')
            ->will($this->returnValue($metadataFactory));

        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry->expects($this->once())->method('getManagerForClass')->with(self::TEST_ENTITY_NAME)
            ->will($this->returnValue($em));

        $this->handler->initSearchIndexer($indexer, [self::TEST_ENTITY_NAME => ['alias' => self::TEST_ENTITY_ALIAS]]);
        $this->handler->initDoctrinePropertiesByManagerRegistry($managerRegistry);

        //the main filter check
        $expr
            ->expects($this->once())
            ->method('in')
            ->with('e.'.self::TEST_ID_FIELD, ':entityIds');

        $this->handler->search('query', 0, 10);
    }
}
