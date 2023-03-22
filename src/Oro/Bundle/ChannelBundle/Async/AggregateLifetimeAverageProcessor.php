<?php
namespace Oro\Bundle\ChannelBundle\Async;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ChannelBundle\Async\Topic\AggregateLifetimeAverageTopic;
use Oro\Bundle\ChannelBundle\Entity\LifetimeValueAverageAggregation;
use Oro\Bundle\ChannelBundle\Entity\Repository\LifetimeValueAverageAggregationRepository;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\JobRunner;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;

/**
 * Aggregates an average lifetime value
 */
class AggregateLifetimeAverageProcessor implements MessageProcessorInterface, TopicSubscriberInterface
{
    private ManagerRegistry $registry;

    private LocaleSettings $localeSettings;

    private JobRunner $jobRunner;

    public function __construct(ManagerRegistry $registry, LocaleSettings $localeSettings, JobRunner $jobRunner)
    {
        $this->registry = $registry;
        $this->localeSettings = $localeSettings;
        $this->jobRunner = $jobRunner;
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session): string
    {
        $messageBody = $message->getBody();

        $result = $this->jobRunner->runUniqueByMessage($message, function () use ($messageBody) {
            /** @var LifetimeValueAverageAggregationRepository $repository */
            $repository  = $this->registry->getRepository(LifetimeValueAverageAggregation::class);
            if ($messageBody['force']) {
                $repository->clearTableData(!$messageBody['use_truncate']);
            }

            $repository->aggregate($this->localeSettings->getTimeZone(), $messageBody['force']);

            return true;
        });

        return $result ? self::ACK : self::REJECT;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics(): array
    {
        return [AggregateLifetimeAverageTopic::getName()];
    }
}
