<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Unit\Entity;

class EmailCampaignStatisticsTest extends AbstractEntityTestCase
{
    /**
     * {@inheritDoc}
     */
    public function getEntityFQCN()
    {
        return 'OroCRM\Bundle\CampaignBundle\Entity\EmailCampaignStatistics';
    }

    /**
     * {@inheritDoc}
     */
    public function getSetDataProvider()
    {
        $campaign = $this->getMockBuilder('OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign')
            ->disableOriginalConstructor()
            ->getMock();
        $marketingListItem = $this->getMockBuilder('OroCRM\Bundle\MarketingListBundle\Entity\MarketingListItem')
            ->disableOriginalConstructor()
            ->getMock();
        $date = new \DateTime();

        return [

            'createdAt' => ['createdAt', $date, $date],
            'emailCampaign' => ['emailCampaign', $campaign, $campaign],
            'marketingListItem' => ['marketingListItem', $marketingListItem, $marketingListItem],
        ];
    }

    public function testLifecycleCallbacks()
    {
        $this->entity->prePersist();
        $this->assertInstanceOf('\DateTime', $this->entity->getCreatedAt());
    }
}
