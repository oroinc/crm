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
use OroCRM\Bundle\CampaignBundle\Provider\EmailTransportProvider;

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
     * @var EmailTransportProvider
     */
    protected $emailTransportProvider;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var TransportInterface
     */
    protected $transport;

    /**
     * @var EmailCampaign
     */
    protected $emailCampaign;

    /**
     * @param MarketingListProvider $marketingListProvider
     * @param ConfigManager $configManager
     * @param MarketingListItemConnector $marketingListItemConnector
     * @param ContactInformationFieldsProvider $contactInformationFieldsProvider
     * @param ManagerRegistry $registry
     * @param EmailTransportProvider $emailTransportProvider
     */
    public function __construct(
        MarketingListProvider $marketingListProvider,
        ConfigManager $configManager,
        MarketingListItemConnector $marketingListItemConnector,
        ContactInformationFieldsProvider $contactInformationFieldsProvider,
        ManagerRegistry $registry,
        EmailTransportProvider $emailTransportProvider
    ) {
        $this->marketingListProvider = $marketingListProvider;
        $this->configManager = $configManager;
        $this->marketingListItemConnector = $marketingListItemConnector;
        $this->contactInformationFieldsProvider = $contactInformationFieldsProvider;
        $this->registry = $registry;
        $this->emailTransportProvider = $emailTransportProvider;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param EmailCampaign $emailCampaign
     */
    public function setEmailCampaign(EmailCampaign $emailCampaign)
    {
        $this->emailCampaign = $emailCampaign;

        $this->transport = $this->emailTransportProvider
            ->getTransportByName($emailCampaign->getTransport());
    }

    public function send()
    {
        $this->assertTransport();
        $marketingList = $this->emailCampaign->getMarketingList();
        /** @var EntityManager $manager */
        $manager = $this->registry->getManager();

        foreach ($this->getIterator() as $entity) {
            $to = $this->contactInformationFieldsProvider->getQueryContactInformationFields(
                $marketingList->getSegment(),
                $entity,
                ContactInformationFieldsProvider::CONTACT_INFORMATION_SCOPE_EMAIL
            );

            try {
                $manager->beginTransaction();
                // Do actual send
                $this->transport->send(
                    $this->emailCampaign,
                    $entity,
                    [$this->getSenderEmail() => $this->getSenderName()],
                    $to
                );

                // Mark marketing list item as contacted
                $marketingListItem = $this->marketingListItemConnector
                    ->contact($marketingList, $entity->getId());

                // Record email campaign contact statistic
                $statisticsRecord = new EmailCampaignStatistics();
                $statisticsRecord->setEmailCampaign($this->emailCampaign)
                    ->setMarketingListItem($marketingListItem);
                $this->saveEntity($manager, $statisticsRecord);
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

        $this->emailCampaign->setSent(true);
        $manager->persist($this->emailCampaign);
        $manager->flush();
    }

    /**
     * @param EntityManager $manager
     * @param EmailCampaignStatistics $statisticsRecord
     */
    protected function saveEntity(EntityManager $manager, EmailCampaignStatistics $statisticsRecord)
    {
        $manager->persist($statisticsRecord);
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
     * @return string
     */
    protected function getSenderEmail()
    {
        if ($senderEmail = $this->emailCampaign->getSenderEmail()) {
            return $senderEmail;
        }

        return $this->configManager->get('oro_crm_campaign.campaign_sender_email');
    }

    /**
     * @return string
     */
    protected function getSenderName()
    {
        if ($senderName = $this->emailCampaign->getSenderName()) {
            return $senderName;
        }

        return $this->configManager->get('oro_crm_campaign.campaign_sender_name');
    }

    /**
     * @return \Iterator
     */
    protected function getIterator()
    {
        return $this->marketingListProvider
            ->getMarketingListEntitiesIterator($this->emailCampaign->getMarketingList());
    }
}
