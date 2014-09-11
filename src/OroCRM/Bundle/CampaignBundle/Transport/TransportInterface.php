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
     * Get transport name.
     *
     * @return string
     */
    public function getName();

    /**
     * Get label used for transport selection.
     *
     * @return string
     */
    public function getLabel();

    /**
     * Returns form type name needed to setup transport.
     *
     * @return string
     */
    public function getSettingsFormType();

    /**
     * Returns entity name needed to store transport settings.
     *
     * @return string
     */
    public function getSettingsEntityFQCN();
}
