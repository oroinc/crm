<?php

namespace OroCRM\Bundle\CampaignBundle\Transport;

use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;

interface TransportInterface
{
    /**
     * @param EmailCampaign $campaign
     * @param string $entity
     * @param string[] $from Associative array, key is sender email, value is sender name
     * @param string[] $to
     * @return mixed
     */
    public function send(EmailCampaign $campaign, $entity, array $from, array $to);

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getDisplayName();
}
