<?php

namespace Oro\Bundle\MagentoBundle\Command;

use Oro\Bundle\CronBundle\Command\CronCommandInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository as IntegrationRepository;
use Oro\Bundle\MagentoBundle\Async\Topics;
use Oro\Bundle\MagentoBundle\Provider\ChannelType;
use Oro\Component\Log\OutputLogger;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class SyncCartExpirationCommand extends Command implements CronCommandInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function getDefaultDefinition()
    {
        return '0 3 * * *';
    }

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName('oro:cron:magento:cart:expiration')
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

        /** @var IntegrationRepository $repository */
        $repository = $this->getDoctrine()->getRepository(Integration::class);

        $integrationId = $input->getOption('channel-id');
        if ($integrationId) {
            $channel = $repository->getOrLoadById($integrationId);
            if (!$channel) {
                throw new \InvalidArgumentException('Integration with given ID not found');
            }

            $channels = [$channel];
        } else {
            $channels = $repository->getConfiguredChannelsForSync(ChannelType::TYPE);
        }

        /** @var Integration $channel */
        foreach ($channels as $channel) {
            $logger->info(sprintf('Run sync for "%s" channel.', $channel->getName()));

            $this->getMessageProducer()->send(
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
     * @return RegistryInterface
     */
    private function getDoctrine()
    {
        return $this->container->get('doctrine');
    }

    /**
     * @return MessageProducerInterface
     */
    private function getMessageProducer()
    {
        return $this->container->get('oro_message_queue.message_producer');
    }
}
