<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Provider;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Entity\Repository\ChannelRepository;
use OroCRM\Bundle\ChannelBundle\Provider\ChannelsByEntitiesProvider;

class ChannelsByEntitiesProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ChannelsByEntitiesProvider
     */
    protected $provider;

    /**
     * @var ChannelRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $repo;

    protected function setUp()
    {
        $doctrineHelper = $this
            ->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->repo = $this
            ->getMockBuilder('OroCRM\Bundle\ChannelBundle\Entity\Repository\ChannelRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $doctrineHelper
            ->expects($this->once())
            ->method('getEntityRepositoryForClass')
            ->with('OroCRMChannelBundle:Channel')
            ->willReturn($this->repo);

        $this->provider = new ChannelsByEntitiesProvider($doctrineHelper);
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
        $this->repo
            ->expects($this->any())
            ->method('getChannelsByEntities')
            ->with()
            ->willReturnMap([
                [['Entity1', 'Entity2'], true, $channelsForParams1],
                [['Entity1'], true, $channelsForParams2],
                [['Entity1', 'Entity2'], false, $channelsForParams3]

            ]);
        foreach ($data as $item) {
            $result     = $item[0];
            $entities   = $item[1];
            $status     = $item[2];
            $this->assertSame($result, $this->provider->getChannelsByEntities($entities, $status));
        }
    }
}
