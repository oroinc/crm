<?php
namespace OroCRM\Bundle\ChannelBundle\Async;

use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\MessageQueue\Util\JSON;
use OroCRM\Bundle\ChannelBundle\Entity\LifetimeValueAverageAggregation;
use OroCRM\Bundle\ChannelBundle\Entity\Repository\LifetimeValueAverageAggregationRepository;
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
     * @param RegistryInterface $registry
     * @param LocaleSettings $localeSettings
     */
    public function __construct(RegistryInterface $registry, LocaleSettings $localeSettings)
    {
        $this->registry = $registry;
        $this->localeSettings = $localeSettings;
    }

    /**
     * {@inheritdoc}
     */
    public function process(MessageInterface $message, SessionInterface $session)
    {
        // TODO CRM-5839 unique job

        $body = array_replace([
            'force' => false,
            'clear_table_use_delete' => false
        ], JSON::decode($message->getBody()));

        /** @var LifetimeValueAverageAggregationRepository $repository */
        $repository  = $this->registry->getRepository(LifetimeValueAverageAggregation::class);
        if ($body['force']) {
            $repository->clearTableData($body['clear_table_use_delete']);
        }

        $repository->aggregate($this->localeSettings->getTimeZone(), $body['force']);

        return self::ACK;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedTopics()
    {
        return [Topics::AGGREGATE_LIFETIME_AVERAGE];
    }
}
