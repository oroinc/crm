<?php

namespace OroCRM\Bundle\MagentoBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Entity\Repository\ChannelRepository;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use OroCRM\Bundle\MagentoBundle\Async\Topics;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use JMS\JobQueueBundle\Entity\Job;

use Oro\Bundle\SearchBundle\Command\ReindexCommand;
use Oro\Component\Log\OutputLogger;

use OroCRM\Bundle\AnalyticsBundle\Model\RFMMetricStateManager;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\MagentoBundle\Provider\InitialSyncProcessor;

class InitialSyncCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this
            ->setName('oro:magento:initial:sync')
            ->addOption(
                'integration',
                'i',
                InputOption::VALUE_REQUIRED,
                'If option exists sync will be performed for given integration id'
            )
            ->addOption(
                'connector',
                'con',
                InputOption::VALUE_OPTIONAL,
                'If option exists sync will be performed for given connector name'
            )
            ->addArgument(
                'connector-parameters',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'Additional connector parameters array. Format - parameterKey=parameterValue',
                []
            )
            ->setDescription('Run initial synchronization for magento channel.');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $connector = $input->getOption('connector');
        $integrationId = $input->getOption('integration');
        $connectorParameters = $this->getConnectorParameters($input);

        /** @var ChannelRepository $integrationRepository */
        $integrationRepository = $this->getEntityManager()->getRepository(Integration::class);

        $integration = $integrationRepository->getOrLoadById($integrationId);
        if (false == $integration) {
            throw new \LogicException(sprintf('Integration with given ID "%d" not found', $integrationId));
        }

        $output->writeln(sprintf('Run initial sync for "%s" integration.', $integration->getName()));

        $message = new Message();
        $message->setPriority(MessagePriority::VERY_LOW);
        $message->setBody([
            'integration_id' => $integration->getId(),
            'connector' => $connector,
            'connector_parameters' => $connectorParameters,
        ]);

        $this->getMessageProducer()->send(Topics::SYNC_INITIAL_INTEGRATION, $message);

        $output->writeln('Completed');
    }

    /**
     * Get connector additional parameters array from the input
     *
     * @param InputInterface $input
     *
     * @return array key - parameter name, value - parameter value
     * @throws \LogicException
     */
    private function getConnectorParameters(InputInterface $input)
    {
        $result = [];
        $connectorParameters = $input->getArgument('connector-parameters');
        if (!empty($connectorParameters)) {
            foreach ($connectorParameters as $parameterString) {
                $parameterConfigArray = explode('=', $parameterString);
                if (!isset($parameterConfigArray[1])) {
                    throw new \LogicException(sprintf(
                        'Format for connector parameters is parameterKey=parameterValue. Got `%s`',
                        $parameterString
                    ));
                }
                $result[$parameterConfigArray[0]] = $parameterConfigArray[1];
            }
        }

        return $result;
    }

    /**
     * @return EntityManagerInterface
     */
    private function getEntityManager()
    {
        return $this->container->get('doctrine')->getManager();
    }

    /**
     * @return MessageProducerInterface
     */
    private function getMessageProducer()
    {
        return $this->container->get('oro_message_queue.message_producer');
    }

}
