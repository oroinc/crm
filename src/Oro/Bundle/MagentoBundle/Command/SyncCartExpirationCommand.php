<?php

namespace Oro\Bundle\MagentoBundle\Command;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository as IntegrationRepository;
use Oro\Bundle\MagentoBundle\Async\Topics;
use Oro\Bundle\MagentoBundle\Provider\MagentoChannelType;
use Oro\Component\Log\OutputLogger;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Runs synchronization for magento channels to process expiration of merged carts
 */
class SyncCartExpirationCommand extends Command implements CronCommandInterface
{
    /** @var string */
    protected static $defaultName = 'oro:cron:magento:cart:expiration';

    /** @var ManagerRegistry */
    private $registry;

    /** @var MessageProducerInterface */
    private $messageProducer;

    /**
     * @param ManagerRegistry $doctrine
     * @param MessageProducerInterface $messageProducer
     */
    public function __construct(ManagerRegistry $doctrine, MessageProducerInterface $messageProducer)
    {
        parent::__construct();

        $this->registry = $doctrine;
        $this->messageProducer = $messageProducer;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultDefinition()
    {
        return '0 3 * * *';
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return ($this->getIntegrationRepository()->countActiveIntegrations(MagentoChannelType::TYPE) > 0);
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->addOption(
                'channel-id',
                'c',
                InputOption::VALUE_OPTIONAL,
                'If option exists sync will be performed for given channel id'
            )
            ->setDescription('Runs synchronization for magento channels to process expiration of merged carts');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $logger    = new OutputLogger($output);

        $repository = $this->getIntegrationRepository();

        $integrationId = $input->getOption('channel-id');
        if ($integrationId) {
            $channel = $repository->getOrLoadById($integrationId);
            if (!$channel) {
                throw new \InvalidArgumentException('Integration with given ID not found');
            }

            $channels = [$channel];
        } else {
            $channels = $repository->getConfiguredChannelsForSync(MagentoChannelType::TYPE);
        }

        /** @var Integration $channel */
        foreach ($channels as $channel) {
            $logger->info(sprintf('Run sync for "%s" channel.', $channel->getName()));

            $this->messageProducer->send(
                Topics::SYNC_CART_EXPIRATION_INTEGRATION,
                new Message(
                    ['integrationId' => $channel->getId()],
                    MessagePriority::VERY_LOW
                )
            );
        }

        $logger->info('Completed');
    }

    /**
     * @return IntegrationRepository
     */
    private function getIntegrationRepository()
    {
        return $this->registry->getRepository(Integration::class);
    }
}
