<?php

namespace OroCRM\Bundle\CampaignBundle\Tests\Unit\Provider;

use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;
use OroCRM\Bundle\CampaignBundle\Transport\TransportInterface;
use OroCRM\Bundle\CampaignBundle\Transport\VisibilityTransportInterface;

class TransportStub implements TransportInterface, VisibilityTransportInterface
{
    /**
     * {@inheritdoc}
     */
    public function send(EmailCampaign $campaign, $entity, array $from, array $to)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsFormType()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsEntityFQCN()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function isVisibleInForm()
    {
    }
}
