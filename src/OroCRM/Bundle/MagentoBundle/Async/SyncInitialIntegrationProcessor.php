<?php

namespace OroCRM\Bundle\MagentoBundle\Async;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\PlatformBundle\Manager\OptionalListenerManager;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use OroCRM\Bundle\AnalyticsBundle\Model\RFMMetricStateManager;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\Provider\InitialSyncProcessor;

class SyncInitialIntegrationProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    /**
     * @var DoctrineHelper
     */
    private $doctrineHelper;

    /**
     * @var InitialSyncProcessor
     */
    private $initialSyncProcessor;

    /**
     * @var OptionalListenerManager
     */
    private $optionalListenerManager;

    /**
     * @var RFMMetricStateManager
     */
    private $rfmMetricStateManager;

    /**
     * @var JobRunner
     */
    private $jobRunner;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param InitialSyncProcessor $initialSyncProcessor
     * @param OptionalListenerManager $optionalListenerManager
     * @param RFMMetricStateManager $rfmMetricStateManager
     * @param JobRunner $jobRunner
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        InitialSyncProcessor $initialSyncProcessor,
        OptionalListenerManager $optionalListenerManager,
        RFMMetricStateManager $rfmMetricStateManager,
        JobRunner $jobRunner
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->initialSyncProcessor = $initialSyncProcessor;
        $this->optionalListenerManager = $optionalListenerManager;
        $this->rfmMetricStateManager = $rfmMetricStateManager;
        $this->jobRunner = $jobRunner;
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        // TODO CRM-5838 message could be redelivered on dbal transport if run for a long time.

        $body = JSON::decode($message->getBody());
        $body = array_replace_recursive([
            'integration_id' => null,
            'connector' => null,
            'connector_parameters' => [],
        ], $body);

        if (false == $body['integration_id']) {
            throw new \LogicException('The message invalid. It must have integration_id set');
        }

        $jobName = 'orocrm_magento:sync_initial_integration:'.$body['integration_id'];
        $ownerId = $message->getMessageId();

        $result = $this->jobRunner->runUnique($ownerId, $jobName, function () use ($body) {
            // Disable search listeners to increase the performance
            $this->disableOptionalListeners();

            /** @var EntityManagerInterface $em */
            $em = $this->doctrineHelper->getEntityManager(Integration::class);
            $em->getConnection()->getConfiguration()->setSQLLogger(null);

            /** @var Integration $integration */
            $integration = $em->find(Integration::class, $body['integration_id']);
            if (false == $integration) {
                return false;
            }
            if (false == $integration->isEnabled()) {
                return false;
            }

            $result = $this->initialSyncProcessor->process(
                $integration,
                $body['connector'],
                $body['connector_parameters']
            );

            if ($result) {
                $this->scheduleAnalyticRecalculation($integration);
                $this->scheduleSearchReindex();
            }

            return $result;
        });

        return $result ? self::ACK : self::REJECT;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [Topics::SYNC_INITIAL_INTEGRATION];
    }

    private function disableOptionalListeners()
    {
        $disabledOptionalListeners = [
            'oro_search.index_listener',
            'oro_entity.event_listener.entity_modify_created_updated_properties_listener'
        ];

        $knownListeners  = $this->optionalListenerManager->getListeners();
        foreach ($disabledOptionalListeners as $listenerId) {
            if (in_array($listenerId, $knownListeners, true)) {
                $this->optionalListenerManager->disableListener($listenerId);
            }
        }
    }

    /**
     * @param Integration $integration
     */
    private function scheduleAnalyticRecalculation(Integration $integration)
    {
        /** @var Channel $channel */
        $channel = $this->doctrineHelper->getEntityRepository(Channel::class)->findOneBy([
            'dataSource' => $integration
        ]);

        if (!$channel) {
            throw new \LogicException(sprintf(
                'The integration does not have channel associated with it. Integration: %s',
                $integration->getId()
            ));
        }

        $this->rfmMetricStateManager->scheduleRecalculation($channel);
    }

    /**
     * Add jobs to reindex magento entities
     */
    private function scheduleSearchReindex()
    {
        // TODO CRM-5838 implement this method when search PR is merged

        $indexedEntities = [Order::class, Cart::class, Customer::class];
    }
}
