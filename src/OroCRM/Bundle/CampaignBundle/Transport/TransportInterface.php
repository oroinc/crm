<?php

namespace OroCRM\Bundle\CampaignBundle\Transport;

use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;

interface TransportInterface
{
    /**
     * @param EmailCampaign $campaign
     * @param string $entity
     * @param string $from
     * @param string[] $to
     * @return mixed
     */
    public function send(EmailCampaign $campaign, $entity, $from, array $to);
}
