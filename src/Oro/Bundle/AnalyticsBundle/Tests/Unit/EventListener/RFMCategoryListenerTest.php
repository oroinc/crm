<?php

namespace Oro\Bundle\AnalyticsBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Oro\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;
use Oro\Bundle\AnalyticsBundle\EventListener\RFMCategoryListener;
use Oro\Bundle\AnalyticsBundle\Model\RFMMetricStateManager;
use Oro\Bundle\AnalyticsBundle\Service\CalculateAnalyticsScheduler;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Event\ChannelSaveEvent;

class RFMCategoryListenerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|RFMMetricStateManager
     */
    protected $manager;

    /**
     * @var RFMCategoryListener
     */
    protected $listener;

    /**
     * @var CalculateAnalyticsScheduler
     */
    protected $scheduler;

    protected function setUp(): void
    {
        $this->manager = $this->getMockBuilder('Oro\Bundle\AnalyticsBundle\Model\RFMMetricStateManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->scheduler = $this->createCalculateAnalyticsSchedulerMock();

        $this->listener = new RFMCategoryListener(
            $this->manager,
            $this->scheduler,
            'Oro\Bundle\AnalyticsBundle\Entity\RFMMetricCategory',
            'Oro\Bundle\ChannelBundle\Entity\Channel'
        );
    }

    /**
     * @param array $updateEntities
     * @param array $insertEntities
     * @param array $deleteEntities
     * @param int $expectedResetMetrics
     * @param int $expectedScheduleRecalculation
     *
     * @dataProvider entitiesDataProvider
     */
    public function testEvents(
        array $updateEntities,
        array $insertEntities,
        array $deleteEntities,
        $expectedResetMetrics = 0,
        $expectedScheduleRecalculation = 0
    ) {
        /** @var \PHPUnit\Framework\MockObject\MockObject|EntityManager $em */
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
        $uow = $this->getMockBuilder('Doctrine\ORM\UnitOfWork')->disableOriginalConstructor()->getMock();

        $em->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($uow));

        $uow->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->will($this->returnValue($updateEntities));

        $uow->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->will($this->returnValue($insertEntities));

        $uow->expects($this->once())
            ->method('getScheduledEntityDeletions')
            ->will($this->returnValue($deleteEntities));

        $this->manager->expects($this->exactly($expectedResetMetrics))
            ->method('resetMetrics');

        $this->scheduler->expects($this->exactly($expectedScheduleRecalculation))
            ->method('scheduleForChannel');

        $args = new OnFlushEventArgs($em);

        $this->listener->onFlush($args);

        $event = new ChannelSaveEvent(new Channel());

        $this->listener->onChannelSucceedSave($event);
    }

    /**
     * @return array
     */
    public function entitiesDataProvider()
    {
        $channel = $this->getChannel();
        $droppedChannel = $this->getChannel(1, ['rfm_require_drop' => true]);
        $category = $this->getCategory($channel);

        return [
            'without reset' => [[], [], []],
            'not supported entities' => [[new \stdClass()], [new \stdClass()], [new \stdClass()]],
            'one channel insertions' => [[$category], [], [], 1, 1],
            'updates' => [[], [$category], [], 1, 1],
            'deletions' => [[], [], [$category], 1, 1],
            'full' => [[$category], [$category], [$category], 1, 1],
            'two channels' => [[$category], [$this->getCategory($this->getChannel(2))], [$category], 2, 2],
            'three channels' => [
                'updateEntities' => [$category],
                'insertEntities' => [$this->getCategory($this->getChannel(2))],
                'deleteEntities' => [$this->getCategory($this->getChannel(3))],
                'expectedResetMetrics' => 3,
                'expectedScheduleRecalculation' => 3,
            ],
            'channel without key' => [[], [$channel], []],
            'channel to drop' => [[], [$this->getChannel(1, ['rfm_require_drop' => true])], [], 1],
            'channel with category' => [
                'updateEntities' => [$category],
                'insertEntities' => [$this->getChannel(2, ['rfm_require_drop' => true])],
                'deleteEntities' => [],
                'expectedResetMetrics' => 2,
                'expectedScheduleRecalculation' => 1,
            ],
            'skip dropped channel recalculation' => [
                'updateEntities' => [$this->getCategory($droppedChannel)],
                'insertEntities' => [$droppedChannel],
                'deleteEntities' => [],
                'expectedResetMetrics' => 1,
                'expectedScheduleRecalculation' => 0,
            ],
        ];
    }

    /**
     * @param int $channelId
     * @param array $data
     *
     * @return Channel
     */
    protected function getChannel($channelId = 1, array $data = [])
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|Channel $channel */
        $channel = $this->createMock('Oro\Bundle\ChannelBundle\Entity\Channel');

        $channel->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($channelId));

        $channel->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($data));

        return $channel;
    }

    /**
     * @param Channel $channel
     *
     * @return RFMMetricCategory
     */
    protected function getCategory($channel)
    {
        $category = new RFMMetricCategory();

        $category->setChannel($channel);

        return $category;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|CalculateAnalyticsScheduler
     */
    private function createCalculateAnalyticsSchedulerMock()
    {
        return $this->createMock(CalculateAnalyticsScheduler::class);
    }
}
