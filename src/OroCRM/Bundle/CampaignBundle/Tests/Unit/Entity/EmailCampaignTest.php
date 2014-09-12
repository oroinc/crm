<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Unit\Entity;

use Oro\Bundle\EmailBundle\Entity\EmailTemplate;
use Oro\Bundle\UserBundle\Entity\User;

use OroCRM\Bundle\CampaignBundle\Entity\Campaign;
use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

class EmailCampaignTest extends AbstractEntityTestCase
{
    /**
     * {@inheritDoc}
     */
    public function getEntityFQCN()
    {
        return 'OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign';
    }

    /**
     * {@inheritDoc}
     */
    public function getSetDataProvider()
    {
        $campaign      = new Campaign();
        $marketingList = new MarketingList();
        $owner         = new User();
        $date          = new \DateTime('now', new \DateTimeZone('UTC'));
        $transportSettings = $this->getMockForAbstractClass('OroCRM\Bundle\CampaignBundle\Entity\TransportSettings');

        return [
            'name'          => ['name', 'test', 'test'],
            'description'   => ['description', 'test', 'test'],
            'campaign'      => ['campaign', $campaign, $campaign],
            'sent'          => ['sent', $date, $date],
            'sentAt'        => ['sentAt', true, true],
            'schedule'      => ['schedule', EmailCampaign::SCHEDULE_DEFERRED, EmailCampaign::SCHEDULE_DEFERRED],
            'scheduledFor'  => ['scheduledFor', $date, $date],
            'marketingList' => ['marketingList', $marketingList, $marketingList],
            'owner'         => ['owner', $owner, $owner],
            'updatedAt'     => ['updatedAt', $date, $date],
            'createdAt'     => ['createdAt', $date, $date],
            'senderEmail'   => ['senderEmail', 'test@test.com', 'test@test.com'],
            'senderName'    => ['senderName', 'name', 'name'],
            'transport'     => ['transport', 'transport', 'transport'],
            'transportSettings' => ['transportSettings', $transportSettings, $transportSettings]
        ];
    }

    public function testLifecycleCallbacks()
    {
        $date = new \DateTime('now', new \DateTimeZone('UTC'));

        $this->entity->prePersist();
        $this->entity->preUpdate();

        $this->assertEquals($date->format('Y-m-d'), $this->entity->getCreatedAt()->format('Y-m-d'));
        $this->assertEquals($date->format('Y-m-d'), $this->entity->getUpdatedAt()->format('Y-m-d'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Schedule type unknown is not know. Known types are manual, deferred
     */
    public function testUnknownSchedule()
    {
        $entity = new EmailCampaign();
        $entity->setSchedule('unknown');
    }


    public function testGetEntityName()
    {
        $marketingList = new MarketingList();
        $marketingList->setEntity('\stdClass');
        $campaign = new EmailCampaign();
        $this->assertNull($campaign->getEntityName());

        $campaign->setMarketingList($marketingList);
        $this->assertEquals($marketingList->getEntity(), $campaign->getEntityName());
    }
}
