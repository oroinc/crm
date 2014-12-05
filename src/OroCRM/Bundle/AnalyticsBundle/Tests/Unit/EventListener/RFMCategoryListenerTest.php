<?php

namespace OroCRM\Bundle\AnalyticsBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;

use OroCRM\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;
use OroCRM\Bundle\AnalyticsBundle\EventListener\RFMCategoryListener;
use OroCRM\Bundle\AnalyticsBundle\Model\RFMMetricStateManager;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class RFMCategoryListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|RFMMetricStateManager
     */
    protected $manager;

    /**
     * @var RFMCategoryListener
     */
    protected $listener;

    protected function setUp()
    {
        $this->manager = $this->getMockBuilder('OroCRM\Bundle\AnalyticsBundle\Model\RFMMetricStateManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new RFMCategoryListener(
            $this->manager,
            'OroCRM\Bundle\AnalyticsBundle\Entity\RFMMetricCategory',
            'OroCRM\Bundle\ChannelBundle\Entity\Channel'
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
        /** @var \PHPUnit_Framework_MockObject_MockObject|EntityManager $em */
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

        $this->manager->expects($this->exactly($expectedScheduleRecalculation))
            ->method('scheduleRecalculation');

        $args = new OnFlushEventArgs($em);

        $this->listener->onFlush($args);
    }

    /**
     * @return array
     */
    public function entitiesDataProvider()
    {
        return [
            'without reset' => [[], [], []],
            'not supported entities' => [[new \stdClass()], [new \stdClass()], [new \stdClass()]],
            'one channel insertions' => [[$this->getCategory(1)], [], [], 1, 1],
            'updates' => [[], [$this->getCategory(1)], [], 1, 1],
            'deletions' => [[], [], [$this->getCategory(1)], 1, 1],
            'full' => [[$this->getCategory(1)], [$this->getCategory(1)], [$this->getCategory(1)], 1, 1],
            'two channels' => [[$this->getCategory(1)], [$this->getCategory(2)], [$this->getCategory(1)], 2, 2],
            'three channels' => [[$this->getCategory(1)], [$this->getCategory(2)], [$this->getCategory(3)], 3, 3],
            'channel without key' => [[], [$this->getChannel()], []],
            'channel to drop' => [[], [$this->getChannel(['rfm_require_drop' => true])], [], 1],
            'channel with category' => [
                [$this->getCategory(1)],
                [$this->getChannel(['rfm_require_drop' => true])],
                [],
                2,
                1
            ],
        ];
    }

    /**
     * @param array $data
     *
     * @return Channel
     */
    protected function getChannel(array $data = [])
    {
        $channel = new Channel();

        $channel->setData($data);

        return $channel;
    }

    /**
     * @param int $channelId
     *
     * @return RFMMetricCategory
     */
    protected function getCategory($channelId)
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Channel $channel */
        $channel = $this->getMock('OroCRM\Bundle\ChannelBundle\Entity\Channel');

        $channel->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($channelId));

        $category = new RFMMetricCategory();

        $category->setChannel($channel);

        return $category;
    }
}
