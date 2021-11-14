<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Provider;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Entity\Repository\ChannelRepository;
use Oro\Bundle\ChannelBundle\Provider\ChannelsByEntitiesProvider;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class ChannelsByEntitiesProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var ChannelsByEntitiesProvider */
    private $provider;

    /** @var ChannelRepository|\PHPUnit\Framework\MockObject\MockObject */
    private $repo;

    /** @var AclHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $aclHelper;

    protected function setUp(): void
    {
        $this->repo = $this->createMock(ChannelRepository::class);
        $this->aclHelper = $this->createMock(AclHelper::class);

        $doctrineHelper = $this->createMock(DoctrineHelper::class);
        $doctrineHelper->expects($this->once())
            ->method('getEntityRepositoryForClass')
            ->with('OroChannelBundle:Channel')
            ->willReturn($this->repo);

        $this->aclHelper->expects($this->any())
            ->method('apply')
            ->willReturnArgument(0);

        $this->provider = new ChannelsByEntitiesProvider($doctrineHelper, $this->aclHelper);
    }

    public function testGetChannelsByEntities()
    {
        $channelsForParams1 = [new Channel(), new Channel()];
        $channelsForParams2 = [new Channel(), new Channel()];
        $channelsForParams3 = [new Channel(), new Channel()];
        $data = [
            'entity set#1 and status = true: do not using cache'           => [
                $channelsForParams1,
                ['Entity1', 'Entity2'],
                true
            ],
            'entity set#2: do not using cache'                             => [
                $channelsForParams2,
                ['Entity1'],
                true
            ],
            'entity set#1 and status = false: do not using cache'          => [
                $channelsForParams3,
                ['Entity1', 'Entity2'],
                false
            ],
            'entity set#1 and status = true: using cache'                  => [
                $channelsForParams1,
                ['Entity1', 'Entity2'],
                true
            ],
            'entity set#1 with other order and status = true: using cache' => [
                $channelsForParams1,
                ['Entity2', 'Entity1'],
                true
            ],
        ];
        $this->repo->expects($this->any())
            ->method('getChannelsByEntities')
            ->with()
            ->willReturnMap([
                [['Entity1', 'Entity2'], true, $this->aclHelper, $channelsForParams1],
                [['Entity1'], true, $this->aclHelper, $channelsForParams2],
                [['Entity1', 'Entity2'], false, $this->aclHelper, $channelsForParams3]

            ]);
        foreach ($data as $item) {
            [$result, $entities,  $status] = $item;
            $this->assertSame($result, $this->provider->getChannelsByEntities($entities, $status));
        }
    }
}
