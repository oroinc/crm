<?php

namespace OroCRM\Bundle\MagentoBundle\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Component\Log\OutputLogger;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Command\AbstractSyncCronCommand;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;

use OroCRM\Bundle\MagentoBundle\Provider\ChannelType;
use OroCRM\Bundle\MagentoBundle\Provider\CartExpirationProcessor;

class CartExpirationSyncCommand extends AbstractSyncCronCommand
{
    const COMMAND_NAME = 'oro:cron:magento:cart:expiration';

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
            ->setName(static::COMMAND_NAME)
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
        /** @var ChannelRepository $repository */
        $channelId = $input->getOption('channel-id');
        $logger    = new OutputLogger($output);

        if ($this->isJobRunning($channelId)) {
            $logger->warning('Job already running. Terminating....');

            return 0;
        }

        /** @var CartExpirationProcessor $processor */
        $processor  = $this->getService('orocrm_magento.provider.cart_expiration_processor');
        $repository = $this->getService('doctrine.orm.entity_manager')->getRepository('OroIntegrationBundle:Channel');
        if ($channelId) {
            $channel = $repository->getOrLoadById($channelId);
            if (!$channel) {
                throw new \InvalidArgumentException('Channel with given ID not found');
            }
            $channels = [$channel];
        } else {
            $channels = $repository->getConfiguredChannelsForSync(ChannelType::TYPE);
        }

        $channels = array_filter(
            $channels,
            function (Channel $channel) {
                $connectors = $channel->getConnectors() ? : [];

                return in_array('cart', $connectors);
            }
        );

        /** @var Channel $channel */
        foreach ($channels as $channel) {
            try {
                $logger->info(sprintf('Run sync for "%s" channel.', $channel->getName()));

                $processor->process($channel);
            } catch (\Exception $e) {
                $logger->critical($e->getMessage(), ['exception' => $e]);
                //process another channel even in case if exception thrown
                continue;
            }
        }

        $logger->info('Completed');

        return 0;
    }
}
