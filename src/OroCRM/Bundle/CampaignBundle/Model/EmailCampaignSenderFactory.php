<?php

namespace OroCRM\Bundle\CampaignBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;

use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;

class EmailCampaignSenderFactory
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $senders = array();

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param EmailCampaign $emailCampaign
     * @return EmailCampaignSender
     */
    public function getSender(EmailCampaign $emailCampaign)
    {
        $sender = $this->container->get('orocrm_campaign.email_campaign.sender');
        $sender->setEmailCampaign($emailCampaign);

        return $sender;
    }
}
