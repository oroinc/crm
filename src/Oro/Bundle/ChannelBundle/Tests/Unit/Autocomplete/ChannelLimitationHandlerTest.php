<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Autocomplete;

use Oro\Bundle\SearchBundle\Query\Query;
use Oro\Bundle\SearchBundle\Query\Result;
use Oro\Bundle\ChannelBundle\Autocomplete\ChannelLimitationHandler;

class ChannelLimitationHandlerTest extends \PHPUnit_Framework_TestCase
{
    const TEST_ENTITY_NAME            = 'Oro\\Bundle\\ChannelBundle\\Tests\\Unit\\Stubs\\Entity\\StubEntity';
    const TEST_ENTITY_ALIAS           = 'oro_channel_stub';
    const TEST_SEARCH_FIELD           = 'some_field';
    const TEST_CHANNEL_SEARCH_FIELD   = 'some_fieldDataChannel';
    const TEST_CHANNEL_RELATION_FIELD = 'some_fieldDataChannel';
    const TEST_ID_FIELD               = 'id';

    /** @var ChannelLimitationHandler */
    protected $handler;

    protected function setUp()
    {
        $this->handler = new ChannelLimitationHandler(
            self::TEST_ENTITY_NAME,
            [self::TEST_SEARCH_FIELD],
            self::TEST_CHANNEL_RELATION_FIELD,
            self::TEST_CHANNEL_SEARCH_FIELD
        );
    }

    protected function tearDown()
    {
        unset($this->handler);
    }

    /**
     * @dataProvider searchDataProvider
     *
     * @param string $search
     * @param int    $channelId
     * @param int    $page
     * @param int    $perPage
     */
    public function testSearch($search, $channelId, $page, $perPage)
    {
        $indexer = $this->getMockBuilder('Oro\Bundle\SearchBundle\Engine\Indexer')
            ->setMethods(['query', 'select'])
            ->disableOriginalConstructor()->getMock();

        $entityRepository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()->getMock();

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

        $managerRegistry = $this->createMock('Doctrine\Common\Persistence\ManagerRegistry');
        $managerRegistry->expects($this->once())->method('getManagerForClass')->with(self::TEST_ENTITY_NAME)
            ->will($this->returnValue($em));

        $this->handler->initSearchIndexer($indexer, [self::TEST_ENTITY_NAME => ['alias' => self::TEST_ENTITY_ALIAS]]);
        $this->handler->initDoctrinePropertiesByManagerRegistry($managerRegistry);

        $query        = new Query();
        $self         = $this;
        $entityAlias  = self::TEST_ENTITY_ALIAS;
        $channelField = self::TEST_CHANNEL_SEARCH_FIELD;
        $indexer->expects($this->once())->method('select')
            ->will($this->returnValue($query));
        $indexer->expects($this->once())->method('query')
            ->will(
                $this->returnCallback(
                    function (Query $query) use ($self, $entityAlias, $channelField, $channelId) {
                        $self->assertSame([$entityAlias], $query->getFrom());
                        if ($channelId) {
                            $this->assertEquals(
                                'from oro_channel_stub where (integer some_fieldDataChannel = 1 '
                                . 'and text all_text ~ "someQuery") limit 11',
                                $query->getStringQuery()
                            );
                        }

                        return new Result($query);
                    }
                )
            );

        $this->handler->search($search, $page, $perPage);
    }

    /**
     * @return array
     */
    public function searchDataProvider()
    {
        return [
            'search all entities without limitation' => [
                '$search'    => ';',
                '$channelId' => false,
                '$page'      => 1,
                '$perPage'   => 10
            ],
            'search by query and channel'            => [
                '$search'    => 'someQuery;1',
                '$channelId' => 1,
                '$page'      => 1,
                '$perPage'   => 10
            ]
        ];
    }
}
