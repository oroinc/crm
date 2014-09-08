<?php

namespace OroCRM\Bundle\CampaignBundle\Model;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;
use OroCRM\Bundle\CampaignBundle\Transport\TransportInterface;
use OroCRM\Bundle\MarketingListBundle\Model\MarketingListItemConnector;
use OroCRM\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;
use OroCRM\Bundle\MarketingListBundle\Provider\MarketingListProvider;

class EmailCampaignSender
{
    /**
     * @var MarketingListProvider
     */
    protected $marketingListProvider;

    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @var MarketingListItemConnector
     */
    protected $marketingListItemConnector;

    /**
     * @var ContactInformationFieldsProvider
     */
    protected $contactInformationFieldsProvider;

    /**
     * @var TransportInterface
     */
    protected $transport;

    /**
     * @param MarketingListProvider $marketingListProvider
     * @param ConfigManager $configManager
     * @param MarketingListItemConnector $marketingListItemConnector
     * @param ContactInformationFieldsProvider $contactInformationFieldsProvider
     */
    public function __construct(
        MarketingListProvider $marketingListProvider,
        ConfigManager $configManager,
        MarketingListItemConnector $marketingListItemConnector,
        ContactInformationFieldsProvider $contactInformationFieldsProvider
    ) {
        $this->marketingListProvider = $marketingListProvider;
        $this->configManager = $configManager;
        $this->marketingListItemConnector = $marketingListItemConnector;
        $this->contactInformationFieldsProvider = $contactInformationFieldsProvider;
    }

    public function setTransport(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @param EmailCampaign $campaign
     */
    public function send(EmailCampaign $campaign)
    {
        $this->assertTransport();
        $marketingList = $campaign->getMarketingList();

        foreach ($this->getIterator($campaign) as $entity) {
            $from = $this->getFromEmail($campaign);
            $to = $this->contactInformationFieldsProvider->getQueryContactInformationFields(
                $marketingList->getSegment(),
                $entity,
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL
            );

            // Do actual send
            $this->transport->send($campaign, $entity, $from, $to);

            // Mark marketing list item as contacted
            $this->marketingListItemConnector->contact($marketingList, $entity->getId());
        }
    }

    /**
     * Assert that transport is present.
     *
     * @throws \RuntimeException
     */
    protected function assertTransport()
    {
        if (!$this->transport) {
            throw new \RuntimeException('Transport is required to perform send');
        }
    }

    /**
     * @param EmailCampaign $campaign
     *
     * @return string
     */
    protected function getFromEmail(EmailCampaign $campaign)
    {
        if ($fromEmail = $campaign->getFromEmail()) {
            return $fromEmail;
        }

        return $this->configManager->get('oro_crm_campaign.campaign_from_email');
    }

    /**
     * @param EmailCampaign $campaign
     * @return \Iterator
     */
    protected function getIterator(EmailCampaign $campaign)
    {
        return $this->marketingListProvider->getMarketingListEntitiesIterator($campaign->getMarketingList());
    }
}
