<?php
namespace Oro\Bundle\ChannelBundle\Async;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use Oro\Bundle\ChannelBundle\Entity\LifetimeValueAverageAggregation;
use Oro\Bundle\ChannelBundle\Entity\Repository\LifetimeValueAverageAggregationRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AggregateLifetimeAverageProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var LocaleSettings
     */
    private $localeSettings;

    /**
     * @var JobRunner
     */
    private $jobRunner;

    /**
     * @param RegistryInterface $registry
     * @param LocaleSettings $localeSettings
     * @param JobRunner $jobRunner
     */
    public function __construct(RegistryInterface $registry, LocaleSettings $localeSettings, JobRunner $jobRunner)
    {
        $this->registry = $registry;
        $this->localeSettings = $localeSettings;
        $this->jobRunner = $jobRunner;
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        $body = array_replace([
            'force' => false,
            'clear_table_use_delete' => false
        ], JSON::decode($message->getBody()));

        $ownerId = $message->getMessageId();
        $jobName = 'oro_channel:aggregate_lifetime_average';

        $result = $this->jobRunner->runUnique($ownerId, $jobName, function () use ($body) {
            /** @var LifetimeValueAverageAggregationRepository $repository */
            $repository  = $this->registry->getRepository(LifetimeValueAverageAggregation::class);
            if ($body['force']) {
                $repository->clearTableData($body['clear_table_use_delete']);
            }

            $repository->aggregate($this->localeSettings->getTimeZone(), $body['force']);

            return true;
        });

        return $result ? self::ACK : self::REJECT;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [Topics::AGGREGATE_LIFETIME_AVERAGE];
    }
}
