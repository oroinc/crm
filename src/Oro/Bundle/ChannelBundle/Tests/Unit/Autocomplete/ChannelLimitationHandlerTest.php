<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Autocomplete;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ChannelBundle\Autocomplete\ChannelLimitationHandler;
use Oro\Bundle\SearchBundle\Engine\Indexer;
use Oro\Bundle\SearchBundle\Provider\SearchMappingProvider;
use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\SearchBundle\Query\Result;

class ChannelLimitationHandlerTest extends \PHPUnit\Framework\TestCase
{
    private const TEST_ENTITY_NAME = 'Oro\\Bundle\\ChannelBundle\\Tests\\Unit\\Stubs\\Entity\\StubEntity';
    private const TEST_ENTITY_ALIAS = 'oro_channel_stub';
    private const TEST_SEARCH_FIELD = 'some_field';
    private const TEST_CHANNEL_SEARCH_FIELD = 'some_fieldDataChannel';
    private const TEST_CHANNEL_RELATION_FIELD = 'some_fieldDataChannel';
    private const TEST_ID_FIELD = 'id';

    /** @var ChannelLimitationHandler */
    private $handler;

    protected function setUp(): void
    {
        $this->handler = new ChannelLimitationHandler(
            self::TEST_ENTITY_NAME,
            [self::TEST_SEARCH_FIELD],
            self::TEST_CHANNEL_RELATION_FIELD,
            self::TEST_CHANNEL_SEARCH_FIELD
        );
    }

    /**
     * @dataProvider searchDataProvider
     */
    public function testSearch(string $search, ?int $channelId, int $page, int $perPage)
    {
        $indexer = $this->createMock(Indexer::class);

        $entityRepository = $this->createMock(EntityRepository::class);

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

        $query = new Query();
        $self = $this;
        $entityAlias = self::TEST_ENTITY_ALIAS;
        $indexer->expects($this->once())
            ->method('select')
            ->willReturn($query);
        $indexer->expects($this->once())
            ->method('query')
            ->willReturnCallback(function (Query $query) use ($self, $entityAlias, $channelId) {
                $self->assertSame([$entityAlias], $query->getFrom());
                if ($channelId) {
                    $this->assertEquals(
                        'from oro_channel_stub where (integer some_fieldDataChannel = 1 '
                        . 'and text all_text ~ "someQuery") limit 11',
                        $query->getStringQuery()
                    );
                }

                return new Result($query);
            });

        $this->handler->search($search, $page, $perPage);
    }

    public function searchDataProvider(): array
    {
        return [
            'search all entities without limitation' => [
                'search'    => ';',
                'channelId' => null,
                'page'      => 1,
                'perPage'   => 10
            ],
            'search by query and channel'            => [
                'search'    => 'someQuery;1',
                'channelId' => 1,
                'page'      => 1,
                'perPage'   => 10
            ]
        ];
    }
}
