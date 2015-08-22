<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Unit\Model;

use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaignStatistics;
use OroCRM\Bundle\CampaignBundle\Model\EmailCampaignStatisticsConnector;

class EmailCampaignStatisticsConnectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $marketingListItemConnector;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $doctrineHelper;

    /**
     * @var EmailCampaignStatisticsConnector
     */
    protected $connector;

    protected function setUp()
    {
        $this->marketingListItemConnector = $this
            ->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Model\MarketingListItemConnector')
            ->disableOriginalConstructor()
            ->getMock();
        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->connector = new EmailCampaignStatisticsConnector(
            $this->marketingListItemConnector,
            $this->doctrineHelper
        );
    }

    /**
     * @dataProvider existingDataProvider
     * @param bool $existing
     */
    public function testGetStatisticsRecordExisting($existing)
    {
        $entity = new \stdClass();
        $entityId = 1;
        $entityClass = get_class($entity);

        $marketingList = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();

        $emailCampaign = $this->getMockBuilder('OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign')
            ->disableOriginalConstructor()
            ->getMock();
        $emailCampaign->expects($this->exactly(2))
            ->method('getMarketingList')
            ->will($this->returnValue($marketingList));

        $this->doctrineHelper->expects($this->exactly(2))
            ->method('getSingleEntityIdentifier')
            ->with($entity)
            ->will($this->returnValue($entityId));

        $marketingListItem = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingListItem')
            ->disableOriginalConstructor()
            ->getMock();
        $marketingListItem->expects($this->any())
            ->method('getId')
            ->willReturn(42);

        /**
         * Check marketingListItem cache
         */
        $this->marketingListItemConnector->expects($this->once())
            ->method('getMarketingListItem')
            ->with($marketingList, $entityId)
            ->will($this->returnValue($marketingListItem));

        $statisticsRecord = new EmailCampaignStatistics();

        $repository = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $manager = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectManager')
            ->disableArgumentCloning()
            ->getMock();
        $manager->expects($this->exactly(2))
            ->method('getRepository')
            ->with($entityClass)
            ->will($this->returnValue($repository));
        $this->doctrineHelper->expects($this->exactly(2))
            ->method('getEntityManager')
            ->with($entityClass)
            ->will($this->returnValue($manager));

        if ($existing) {
            $repository->expects($this->exactly(2))
                ->method('findOneBy')
                ->with(['emailCampaign' => $emailCampaign, 'marketingListItem' => $marketingListItem])
                ->will($this->returnValue($statisticsRecord));
        } else {
            $repository->expects($this->exactly(2))
                ->method('findOneBy')
                ->with(['emailCampaign' => $emailCampaign, 'marketingListItem' => $marketingListItem])
                ->will($this->returnValue(null));

            $manager->expects($this->exactly(2))
                ->method('persist')
                ->with($this->isInstanceOf('OroCRM\Bundle\CampaignBundle\Entity\EmailCampaignStatistics'));
        }

        $this->connector->setEntityName($entityClass);
        $actualRecord = $this->connector->getStatisticsRecord($emailCampaign, $entity);
        /**
         * Check marketingListItem cache
         */
        $this->connector->getStatisticsRecord($emailCampaign, $entity);

        if (!$existing) {
            $this->assertEquals($emailCampaign, $actualRecord->getEmailCampaign(), 'unexpected email campaign');
            $this->assertEquals(
                $marketingListItem,
                $actualRecord->getMarketingListItem(),
                'unexpected marketing list item campaign'
            );
        } else {
            $this->assertEquals($statisticsRecord, $actualRecord);
        }
    }

    /**
     * @return array
     */
    public function existingDataProvider()
    {
        return [
            'existing' => [true],
            'not existing' => [false]
        ];
    }
}
