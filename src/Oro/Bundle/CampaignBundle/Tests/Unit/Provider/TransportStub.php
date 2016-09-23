<?php

namespace Oro\Bundle\CampaignBundle\Tests\Unit\Provider;

use Oro\Bundle\CampaignBundle\Entity\EmailCampaign;
use Oro\Bundle\CampaignBundle\Transport\TransportInterface;
use Oro\Bundle\CampaignBundle\Transport\VisibilityTransportInterface;

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
