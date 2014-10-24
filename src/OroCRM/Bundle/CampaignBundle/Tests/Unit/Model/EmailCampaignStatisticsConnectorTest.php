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
    protected $registry;

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
        $this->registry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->getMock();
        $this->doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->connector = new EmailCampaignStatisticsConnector(
            $this->marketingListItemConnector,
            $this->registry,
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

        $marketingList = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingList')
            ->disableOriginalConstructor()
            ->getMock();

        $emailCampaign = $this->getMockBuilder('OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign')
            ->disableOriginalConstructor()
            ->getMock();
        $emailCampaign->expects($this->once())
            ->method('getMarketingList')
            ->will($this->returnValue($marketingList));

        $this->doctrineHelper->expects($this->once())
            ->method('getSingleEntityIdentifier')
            ->with($entity)
            ->will($this->returnValue($entityId));

        $marketingListItem = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingListItem')
            ->disableOriginalConstructor()
            ->getMock();

        $this->marketingListItemConnector->expects($this->once())
            ->method('getMarketingListItem')
            ->with($marketingList, $entityId)
            ->will($this->returnValue($marketingListItem));

        $statisticsRecord = new EmailCampaignStatistics();

        $repository = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();

        if ($existing) {
            $repository->expects($this->once())
                ->method('findOneBy')
                ->with(['emailCampaign' => $emailCampaign, 'marketingListItem' => $marketingListItem])
                ->will($this->returnValue($statisticsRecord));
        } else {
            $repository->expects($this->once())
                ->method('findOneBy')
                ->with(['emailCampaign' => $emailCampaign, 'marketingListItem' => $marketingListItem])
                ->will($this->returnValue(null));

            $manager = $this->getMockBuilder('\Doctrine\Common\Persistence\ObjectManager')
                ->disableArgumentCloning()
                ->getMock();
            $manager->expects($this->once())
                ->method('persist')
                ->with($this->isInstanceOf('OroCRM\Bundle\CampaignBundle\Entity\EmailCampaignStatistics'));
            $this->registry->expects($this->once())
                ->method('getManagerForClass')
                ->with(EmailCampaignStatisticsConnector::EMAIL_CAMPAIGN_STATISTICS_ENTITY)
                ->will($this->returnValue($manager));
        }

        $this->registry->expects($this->once())
            ->method('getRepository')
            ->with(EmailCampaignStatisticsConnector::EMAIL_CAMPAIGN_STATISTICS_ENTITY)
            ->will($this->returnValue($repository));

        $actualRecord = $this->connector->getStatisticsRecord($emailCampaign, $entity);

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

    public function existingDataProvider()
    {
        return [
            'existing' => [true],
            'not existing' => [false]
        ];
    }
}
