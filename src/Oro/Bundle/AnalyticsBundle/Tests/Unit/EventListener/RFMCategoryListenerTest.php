<?php

namespace Oro\Bundle\AnalyticsBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;
use Oro\Bundle\AnalyticsBundle\EventListener\RFMCategoryListener;
use Oro\Bundle\AnalyticsBundle\Model\RFMMetricStateManager;
use Oro\Bundle\AnalyticsBundle\Service\CalculateAnalyticsScheduler;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Event\ChannelSaveEvent;

class RFMCategoryListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var RFMMetricStateManager|\PHPUnit\Framework\MockObject\MockObject */
    private $manager;

    /** @var CalculateAnalyticsScheduler */
    private $scheduler;

    /** @var RFMCategoryListener */
    private $listener;

    protected function setUp(): void
    {
        $this->manager = $this->createMock(RFMMetricStateManager::class);
        $this->scheduler = $this->createMock(CalculateAnalyticsScheduler::class);

        $this->listener = new RFMCategoryListener(
            $this->manager,
            $this->scheduler,
            RFMMetricCategory::class,
            Channel::class
        );
    }

    /**
     * @dataProvider entitiesDataProvider
     */
    public function testEvents(
        array $updateEntities,
        array $insertEntities,
        array $deleteEntities,
        int $expectedResetMetrics = 0,
        int $expectedScheduleRecalculation = 0
    ) {
        $em = $this->createMock(EntityManager::class);
        $uow = $this->createMock(UnitOfWork::class);

        $em->expects($this->once())
            ->method('getUnitOfWork')
            ->willReturn($uow);

        $uow->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->willReturn($updateEntities);
        $uow->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->willReturn($insertEntities);
        $uow->expects($this->once())
            ->method('getScheduledEntityDeletions')
            ->willReturn($deleteEntities);

        $this->manager->expects($this->exactly($expectedResetMetrics))
            ->method('resetMetrics');

        $this->scheduler->expects($this->exactly($expectedScheduleRecalculation))
            ->method('scheduleForChannel');

        $args = new OnFlushEventArgs($em);

        $this->listener->onFlush($args);

        $event = new ChannelSaveEvent(new Channel());

        $this->listener->onChannelSucceedSave($event);
    }

    public function entitiesDataProvider(): array
    {
        $channel = $this->getChannel(1);
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

    private function getChannel(int $channelId, array $data = []): Channel
    {
        $channel = $this->createMock(Channel::class);
        $channel->expects($this->any())
            ->method('getId')
            ->willReturn($channelId);
        $channel->expects($this->any())
            ->method('getData')
            ->willReturn($data);

        return $channel;
    }

    private function getCategory(Channel $channel): RFMMetricCategory
    {
        $category = new RFMMetricCategory();
        $category->setChannel($channel);

        return $category;
    }
}
