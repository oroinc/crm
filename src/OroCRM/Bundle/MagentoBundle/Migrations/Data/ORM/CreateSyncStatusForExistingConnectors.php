<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;
use Oro\Bundle\IntegrationBundle\Entity\Status;

use OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use OroCRM\Bundle\MagentoBundle\Provider\AbstractInitialProcessor;
use OroCRM\Bundle\MagentoBundle\Provider\ChannelType;
use OroCRM\Bundle\MagentoBundle\Provider\InitialSyncProcessor;

class CreateSyncStatusForExistingConnectors extends AbstractFixture
{
    /**
     * @var array
     */
    protected $connectorsToMigrate = [
        'customer',
        'order',
        'cart'
    ];

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $channelRepository = $manager->getRepository('OroIntegrationBundle:Channel');
        $magentoIntegrations = $channelRepository->findBy(['type' => ChannelType::TYPE]);

        /** @var Channel $magentoIntegration */
        foreach ($magentoIntegrations as $magentoIntegration) {
            $enabledConnectors = $magentoIntegration->getConnectors();
            $connectorsToMigrate = array_intersect($enabledConnectors, $this->connectorsToMigrate);

            foreach ($connectorsToMigrate as $connector) {
                $initialConnector = $connector . InitialSyncProcessor::INITIAL_CONNECTOR_SUFFIX;
                $existingStatus = $channelRepository->getLastStatusForConnector(
                    $magentoIntegration,
                    $initialConnector,
                    Status::STATUS_COMPLETED
                );
                if (!$existingStatus) {
                    $this->addInitialStatus($channelRepository, $magentoIntegration, $initialConnector);
                }
            }
        }
    }

    /**
     * @param ChannelRepository $repository
     * @param Channel $integration
     * @param string $connector
     */
    protected function addInitialStatus(ChannelRepository $repository, Channel $integration, $connector)
    {
        /** @var MagentoSoapTransport $transport */
        $transport = $integration->getTransport();
        $syncStartDate = $transport->getSyncStartDate();
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $status = new Status();
        $status->setData([AbstractInitialProcessor::INITIAL_SYNCED_TO => $syncStartDate->format(\DateTime::ISO8601)]);
        $status->setConnector($connector);
        $status->setDate($now);
        $status->setChannel($integration);
        $status->setCode(Status::STATUS_COMPLETED);
        $status->setMessage('Automatically added initial connector status.');

        $repository->addStatusAndFlush($integration, $status);
    }
}
