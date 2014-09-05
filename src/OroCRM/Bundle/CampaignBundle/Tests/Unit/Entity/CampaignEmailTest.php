<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Unit\Entity;

use Oro\Bundle\UserBundle\Entity\User;

use OroCRM\Bundle\CampaignBundle\Entity\Campaign;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

class CampaignEmailTest extends AbstractEntityTestCase
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

        /** @todo: relation */
        $template = 1;

        return [
            'name'          => ['campaign', $campaign, $campaign],
            'sent'          => ['sent', true, true],
            'schedule'      => ['schedule', 'schedule', 'schedule'],
            'marketingList' => ['marketingList', $marketingList, $marketingList],
            'owner'         => ['owner', $owner, $owner],
            'template'      => ['template', $template, $template],
            'updatedAt'     => ['template', $date, $date],
            'createdAt'     => ['template', $date, $date],
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
}
