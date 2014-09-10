<?php

namespace OroCRM\Bundle\CampaignBundle\Model;

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;
use OroCRM\Bundle\CampaignBundle\Transport\TransportInterface;
use OroCRM\Bundle\MarketingListBundle\Model\MarketingListItemConnector;
use OroCRM\Bundle\MarketingListBundle\Provider\ContactInformationFieldsProvider;
use OroCRM\Bundle\MarketingListBundle\Provider\MarketingListProvider;
use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaignStatistics;

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
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var TransportInterface
     */
    protected $transport;

    /**
     * @param MarketingListProvider $marketingListProvider
     * @param ConfigManager $configManager
     * @param MarketingListItemConnector $marketingListItemConnector
     * @param ContactInformationFieldsProvider $contactInformationFieldsProvider
     * @param ManagerRegistry $registry
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        MarketingListProvider $marketingListProvider,
        ConfigManager $configManager,
        MarketingListItemConnector $marketingListItemConnector,
        ContactInformationFieldsProvider $contactInformationFieldsProvider,
        ManagerRegistry $registry,
        LoggerInterface $logger = null
    ) {
        $this->marketingListProvider = $marketingListProvider;
        $this->configManager = $configManager;
        $this->marketingListItemConnector = $marketingListItemConnector;
        $this->contactInformationFieldsProvider = $contactInformationFieldsProvider;
        $this->registry = $registry;
        $this->logger = $logger;
    }

    /**
     * @param TransportInterface $transport
     */
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
        /** @var EntityManager $manager */
        $manager = $this->registry->getManager();

        foreach ($this->getIterator($campaign) as $entity) {
            $from = $this->getFromEmail($campaign);
            $to = $this->contactInformationFieldsProvider->getQueryContactInformationFields(
                $marketingList->getSegment(),
                $entity,
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL
            );

            try {
                $manager->beginTransaction();
                // Do actual send
                $this->transport->send($campaign, $entity, $from, $to);

                // Mark marketing list item as contacted
                $marketingListItem = $this->marketingListItemConnector->contact($marketingList, $entity->getId());

                // Record email campaign contact statistic
                $statisticsRecord = new EmailCampaignStatistics();
                $statisticsRecord->setEmailCampaign($campaign)
                    ->setMarketingListItem($marketingListItem);
                $manager->persist($statisticsRecord);

                $manager->flush();
                $manager->commit();
            } catch (\Exception $e) {
                $manager->rollback();

                if ($this->logger) {
                    $this->logger->error(
                        sprintf('Email sending to "%s" failed.', implode(', ', $to)),
                        array('exception' => $e)
                    );
                }
            }
        }

        $campaign->setSent(true);
        $manager->persist($campaign);
        $manager->flush();
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
