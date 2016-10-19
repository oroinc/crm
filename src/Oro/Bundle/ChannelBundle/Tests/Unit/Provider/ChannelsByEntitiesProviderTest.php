<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Provider;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Entity\Repository\ChannelRepository;
use Oro\Bundle\ChannelBundle\Provider\ChannelsByEntitiesProvider;

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

    /**
     * @var AclHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $aclHelper;

    protected function setUp()
    {
        $doctrineHelper = $this
            ->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->repo = $this
            ->getMockBuilder('Oro\Bundle\ChannelBundle\Entity\Repository\ChannelRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $doctrineHelper
            ->expects($this->once())
            ->method('getEntityRepositoryForClass')
            ->with('OroChannelBundle:Channel')
            ->willReturn($this->repo);

        $this->aclHelper = $this->getMockBuilder('Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->aclHelper->expects($this->any())
            ->method('apply')
            ->will($this->returnArgument(0));

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
        $this->repo
            ->expects($this->any())
            ->method('getChannelsByEntities')
            ->with()
            ->willReturnMap([
                [['Entity1', 'Entity2'], true, $this->aclHelper, $channelsForParams1],
                [['Entity1'], true, $this->aclHelper, $channelsForParams2],
                [['Entity1', 'Entity2'], false, $this->aclHelper, $channelsForParams3]

            ]);
        foreach ($data as $item) {
            $result     = $item[0];
            $entities   = $item[1];
            $status     = $item[2];
            $this->assertSame($result, $this->provider->getChannelsByEntities($entities, $status));
        }
    }
}
