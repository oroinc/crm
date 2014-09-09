<?php

namespace OroCRM\Bundle\CampaignBundle\Model;

use Symfony\Component\DependencyInjection\ContainerInterface;

use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;
use OroCRM\Bundle\CampaignBundle\Provider\EmailTransportProvider;

class EmailCampaignSenderFactory
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var EmailTransportProvider
     */
    protected $emailTransportProvider;

    /**
     * @var array
     */
    protected $senders = array();

    /**
     * @param ContainerInterface $container
     * @param EmailTransportProvider $emailTransportProvider
     */
    public function __construct(ContainerInterface $container, EmailTransportProvider $emailTransportProvider)
    {
        $this->container = $container;
        $this->emailTransportProvider = $emailTransportProvider;
    }

    /**
     * @param EmailCampaign $emailCampaign
     * @return EmailCampaignSender
     */
    public function getSender(EmailCampaign $emailCampaign)
    {
        $transportName = $emailCampaign->getTransport();
        if (!isset($this->senders[$transportName])) {
            $transport = $this->emailTransportProvider->getTransportByName($emailCampaign->getTransport());
            $sender = $this->container->get('orocrm_campaign.email_campaign.sender');
            $sender->setTransport($transport);
            $this->senders[$transportName] = $sender;
        }

        return $this->senders[$transportName];
    }
}
