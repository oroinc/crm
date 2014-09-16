<?php

namespace OroCRM\Bundle\CampaignBundle\Model;

use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;

class EmailCampaignSenderBuilder
{
    /**
     * @var EmailCampaignSender
     */
    protected $campaignSender;

    /**
     * @param EmailCampaignSender $campaignSender
     */
    public function __construct(EmailCampaignSender $campaignSender)
    {
        $this->campaignSender = $campaignSender;
    }

    /**
     * @param EmailCampaign $emailCampaign
     * @return EmailCampaignSender
     */
    public function getSender(EmailCampaign $emailCampaign)
    {
        $this->campaignSender->setEmailCampaign($emailCampaign);

        return $this->campaignSender;
    }
}
