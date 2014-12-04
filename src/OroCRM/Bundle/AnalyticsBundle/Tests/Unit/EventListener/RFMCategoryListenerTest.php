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
            'OroCRM\Bundle\AnalyticsBundle\Entity\RFMMetricCategory'
        );
    }

    /**
     * @param array $updateEntities
     * @param array $insertEntities
     * @param array $deleteEntities
     * @param int $expectedChannelResets
     *
     * @dataProvider entitiesDataProvider
     */
    public function testEvents(
        array $updateEntities,
        array $insertEntities,
        array $deleteEntities,
        $expectedChannelResets
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

        $this->manager->expects($this->exactly($expectedChannelResets))
            ->method('resetMetrics');

        $this->manager->expects($this->exactly($expectedChannelResets))
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
            'without reset' => [[], [], [], 0],
            'not supported entities' => [[new \stdClass()], [new \stdClass()], [new \stdClass()], 0],
            'one channel insertions' => [[$this->getCategory(1)], [], [], 1],
            'updates' => [[], [$this->getCategory(1)], [], 1],
            'deletions' => [[], [], [$this->getCategory(1)], 1],
            'full' => [[$this->getCategory(1)], [$this->getCategory(1)], [$this->getCategory(1)], 1],
            'two channels' => [[$this->getCategory(1)], [$this->getCategory(2)], [$this->getCategory(1)], 2],
            'three channels' => [[$this->getCategory(1)], [$this->getCategory(2)], [$this->getCategory(3)], 3],
        ];
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
