<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Model;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Model\ChannelSettingsProvider;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Component\Config\Common\ConfigObject;

class ChannelSettingsProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ChannelSettingsProvider
     */
    protected $provider;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|DoctrineHelper
     */
    protected $doctrineHelper;

    protected function setUp(): void
    {
        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->provider = new ChannelSettingsProvider($this->doctrineHelper, '\stdClass');
    }

    /**
     * @param mixed $value
     *
     *
     * @dataProvider channelIdDataProvider
     */
    public function testChannelId($value)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Channel id value is wrong');

        $this->provider->isTwoWaySyncEnable($value);
    }

    /**
     * @return array
     */
    public function channelIdDataProvider()
    {
        return [
            [false],
            [null],
            [[]],
            [0],
            ['string']
        ];
    }

    /**
     * @dataProvider testDataProvider
     * @param string $method
     * @param array $channels
     * @param bool $expected
     */
    public function testIsTwoWaySyncEnable($method, array $channels, $expected = true)
    {
        $repository = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository
            ->expects($this->any())
            ->method('find')
            ->with($this->isType('integer'))
            ->will(
                $this->returnCallback(
                    function ($id) use ($channels) {
                        if (empty($channels[$id])) {
                            return null;
                        }

                        return $channels[$id];
                    }
                )
            );
        $this->doctrineHelper
            ->expects($this->any())
            ->method('getEntityRepository')
            ->will($this->returnValue($repository));

        $this->assertEquals(false, $this->provider->$method(1));
        $this->assertEquals($expected, $this->provider->$method(2));
        // local cache
        $this->assertEquals($expected, $this->provider->$method(2));
    }

    /**
     * @return array
     */
    public function testDataProvider()
    {
        return [
            [
                'isTwoWaySyncEnable',
                [1 => $this->getChannel(), 2 => $this->getChannel(false, true, false)]
            ],
            [
                'isSupportedExtensionVersion',
                [1 => $this->getChannel(), 2 => $this->getChannel(false, false, true)]
            ],
            [
                'isEnabled',
                [1 => $this->getChannel(), 2 => $this->getChannel(true, false, false)]
            ],
            [
                'isChannelApplicable',
                [1 => $this->getChannel(), 2 => $this->getChannel(true, true, true)]
            ],
            [
                'isChannelApplicable',
                [1 => $this->getChannel(), 2 => $this->getChannel(false, true, true)],
                false
            ],
            [
                'isChannelApplicable',
                [1 => $this->getChannel(), 2 => $this->getChannel(true, false, true)],
                false
            ],
            [
                'isChannelApplicable',
                [1 => $this->getChannel(), 2 => $this->getChannel(true, true, false)],
                false
            ]
        ];
    }

    /**
     * @param bool $expected
     * @param array $channels
     * @param bool $checkExtension
     *
     * @dataProvider channelsDataProvider
     */
    public function testHasApplicableChannels($expected, array $channels, $checkExtension = true)
    {
        $repository = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository
            ->expects($this->any())
            ->method('findBy')
            ->willReturn($channels);
        $this->doctrineHelper
            ->expects($this->any())
            ->method('getEntityRepository')
            ->will($this->returnValue($repository));

        $this->assertEquals($expected, $this->provider->hasApplicableChannels($checkExtension));
    }

    /**
     * @param bool $expected
     * @param array $channels
     * @param bool $checkExtension
     *
     * @dataProvider channelsDataProvider
     */
    public function testHasOrganizationApplicableChannels($expected, array $channels, $checkExtension = true)
    {
        $repository = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository
            ->expects($this->any())
            ->method('findBy')
            ->with($this->arrayHasKey('organization'))
            ->willReturn($channels);
        $this->doctrineHelper
            ->expects($this->any())
            ->method('getEntityRepository')
            ->will($this->returnValue($repository));

        $this->assertEquals(
            $expected,
            $this->provider->hasOrganizationApplicableChannels(new Organization(), $checkExtension)
        );
    }

    /**
     * @return array
     */
    public function channelsDataProvider()
    {
        return [
            [false, []],
            [false, [$this->getChannel(false, false, false, 2)]],
            [true, [$this->getChannel(true, true, true, 2)]],
            [true, [$this->getChannel(true, true, true, 2), $this->getChannel(false, false, false, 3)]],
            [true, [$this->getChannel(true, true, true, 2), $this->getChannel(true, true, true, 3)]],
            [false, [$this->getChannel(false, false, false, 2)], false],
            [true, [$this->getChannel(true, true, false, 2)], false],
            [true, [$this->getChannel(true, true, false, 2), $this->getChannel(false, false, false, 3)], false],
            [true, [$this->getChannel(true, true, false, 2), $this->getChannel(true, true, true, 3)], false]
        ];
    }

    /**
     * @param bool $isEnabled
     * @param bool $isTwoWaySyncEnabled
     * @param bool $isSupportedExtensionVersion
     * @param int $channelId
     *
     * @return Channel|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getChannel(
        $isEnabled = false,
        $isTwoWaySyncEnabled = false,
        $isSupportedExtensionVersion = false,
        $channelId = null
    ) {
        $channel = $this->createMock('Oro\Bundle\IntegrationBundle\Entity\Channel');
        $settings = [];
        if (null !== $isTwoWaySyncEnabled) {
            $settings['isTwoWaySyncEnabled'] = $isTwoWaySyncEnabled;
        }
        $transport = $this->createMock('Oro\Bundle\MagentoBundle\Entity\MagentoTransport');
        $transport->expects($this->any())->method('isSupportedExtensionVersion')
            ->willReturn($isSupportedExtensionVersion);
        $settings = ConfigObject::create($settings);
        $channel->expects($this->any())
            ->method('getSynchronizationSettings')
            ->will($this->returnValue($settings));
        $channel->expects($this->any())
            ->method('isEnabled')
            ->will($this->returnValue($isEnabled));

        if ($channelId) {
            $channel->expects($this->any())
                ->method('getId')
                ->will($this->returnValue($channelId));
        }

        $channel->expects($this->any())
            ->method('getTransport')
            ->will($this->returnValue($transport));

        return $channel;
    }
}
