<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Autocomplete;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\OrganizationBundle\Provider\BusinessUnitAclProvider;
use Oro\Bundle\SalesBundle\Autocomplete\ForecastWidgetBusinessUnitSearchHandler;
use Oro\Bundle\SearchBundle\Engine\Indexer;
use Oro\Bundle\SearchBundle\Provider\SearchMappingProvider;
use Oro\Bundle\SearchBundle\Query\Result;

class ForecastWidgetBusinessUnitSearchHandlerTest extends \PHPUnit\Framework\TestCase
{
    private const TEST_ID_FIELD = 'id';
    private const TEST_ENTITY_NAME = 'OroOrganizationBundle:BusinessUnit';
    private const TEST_ENTITY_ALIAS = 'business_alias';

    /** @var BusinessUnitAclProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $businessAclProvider;

    /** @var ForecastWidgetBusinessUnitSearchHandler */
    private $handler;

    protected function setUp(): void
    {
        $this->businessAclProvider = $this->createMock(BusinessUnitAclProvider::class);

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
            ->willReturn($actualElements);

        $this->businessAclProvider->expects($this->once())
            ->method('getBusinessUnitIds')
            ->willReturn($expectedIds);

        $indexer = $this->createMock(Indexer::class);

        $indexer->expects($this->once())
            ->method('simpleSearch')
            ->willReturn($searchResult);

        $expr = $this->createMock(Expr::class);

        $query = $this->createMock(AbstractQuery::class);
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->expects($this->once())
            ->method('expr')
            ->willReturn($expr);
        $queryBuilder->expects($this->any())
            ->method('setParameter')
            ->with('entityIds', $expectedIds)
            ->willReturnSelf();
        $queryBuilder->expects($this->any())
            ->method('getQuery')
            ->willReturn($query);
        $query->expects($this->any())
            ->method('getResult')
            ->willReturn([]);

        $entityRepository = $this->createMock(EntityRepository::class);

        $entityRepository->expects($this->any())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder);

        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects($this->once())
            ->method('getSingleIdentifierFieldName')
            ->willReturn(self::TEST_ID_FIELD);

        $metadataFactory = $this->createMock(ClassMetadataFactory::class);
        $metadataFactory->expects($this->once())
            ->method('getMetadataFor')
            ->with(self::TEST_ENTITY_NAME)
            ->willReturn($metadata);

        $em = $this->createMock(EntityManager::class);
        $em->expects($this->once())
            ->method('getRepository')
            ->willReturn($entityRepository);
        $em->expects($this->once())
            ->method('getMetadataFactory')
            ->willReturn($metadataFactory);

        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry->expects($this->once())
            ->method('getManagerForClass')
            ->with(self::TEST_ENTITY_NAME)
            ->willReturn($em);

        $searchMappingProvider = $this->createMock(SearchMappingProvider::class);
        $searchMappingProvider->expects($this->once())
            ->method('getEntityAlias')
            ->with(self::TEST_ENTITY_NAME)
            ->willReturn(self::TEST_ENTITY_ALIAS);

        $this->handler->initSearchIndexer($indexer, $searchMappingProvider);
        $this->handler->initDoctrinePropertiesByManagerRegistry($managerRegistry);

        //the main filter check
        $expr->expects($this->once())
            ->method('in')
            ->with('e.'.self::TEST_ID_FIELD, ':entityIds');

        $this->handler->search('query', 0, 10);
    }
}
