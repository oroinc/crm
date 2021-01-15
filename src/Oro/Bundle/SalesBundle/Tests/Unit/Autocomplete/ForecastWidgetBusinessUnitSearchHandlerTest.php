<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Autocomplete;

use Oro\Bundle\SalesBundle\Autocomplete\ForecastWidgetBusinessUnitSearchHandler;
use Oro\Bundle\SearchBundle\Provider\SearchMappingProvider;
use Oro\Bundle\SearchBundle\Query\Result;

class ForecastWidgetBusinessUnitSearchHandlerTest extends \PHPUnit\Framework\TestCase
{
    const TEST_ID_FIELD = 'id';
    const TEST_ENTITY_NAME = 'OroOrganizationBundle:BusinessUnit';
    const TEST_ENTITY_ALIAS = 'business_alias';

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $businessAclProvider;

    /** @var \PHPUnit\Framework\MockObject\MockObject */
    protected $handler;

    protected function setUp(): void
    {
        $this->businessAclProvider = $this
            ->getMockBuilder('Oro\Bundle\OrganizationBundle\Provider\BusinessUnitAclProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->handler = new ForecastWidgetBusinessUnitSearchHandler(
            self::TEST_ENTITY_NAME,
            [],
            $this->businessAclProvider,
            'OroSalesBundle:Opportunity'
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testApplyBusinessUnitAcl()
    {
        $actualElements = [
            new SearchElement(1),
            new SearchElement(2),
            new SearchElement(3)
        ];
        $expectedIds = [1, 2];

        $searchResult = $this->createMock(Result::class);

        $searchResult->expects($this->once())
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
            ->will($this->returnValue($searchResult));

        $expr = $this->getMockBuilder('Doctrine\ORM\Query\Expr')
            ->setMethods(['in'])
            ->disableOriginalConstructor()
            ->getMock();

        $queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
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

        $managerRegistry = $this->createMock('Doctrine\Persistence\ManagerRegistry');
        $managerRegistry->expects($this->once())->method('getManagerForClass')->with(self::TEST_ENTITY_NAME)
            ->will($this->returnValue($em));

        $searchMappingProvider = $this->createMock(SearchMappingProvider::class);
        $searchMappingProvider->expects($this->once())
            ->method('getEntityAlias')
            ->with(self::TEST_ENTITY_NAME)
            ->willReturn(self::TEST_ENTITY_ALIAS);

        $this->handler->initSearchIndexer($indexer, $searchMappingProvider);
        $this->handler->initDoctrinePropertiesByManagerRegistry($managerRegistry);

        //the main filter check
        $expr
            ->expects($this->once())
            ->method('in')
            ->with('e.'.self::TEST_ID_FIELD, ':entityIds');

        $this->handler->search('query', 0, 10);
    }
}
