<?php

namespace Oro\Bundle\ChannelBundle\Async\Topic;

use Oro\Bundle\ChannelBundle\Entity\LifetimeValueHistory;
use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A topic to mass status update of history entries
 */
class LifetimeHistoryStatusUpdateTopic extends AbstractTopic
{
    public const RECORDS_FIELD = 'records';
    public const STATUS_FIELD  = 'status';

    public static function getName(): string
    {
        return 'oro.channel.lifetime_history_status_update';
    }

    public static function getDescription(): string
    {
        return 'Update status of history entries based on data given';
    }

    public function configureMessageBody(OptionsResolver $resolver): void
    {
        $resolver
            ->define(self::STATUS_FIELD)
            ->allowedTypes('int')
            ->allowedValues(LifetimeValueHistory::STATUS_OLD, LifetimeValueHistory::STATUS_NEW)
            ->default(LifetimeValueHistory::STATUS_OLD);

        $resolver
            ->define(self::RECORDS_FIELD)
            ->allowedTypes('array');
    }

    public static function createMessage(array $records, int $status = LifetimeValueHistory::STATUS_OLD): array
    {
        return [
            self::RECORDS_FIELD => $records,
            self::STATUS_FIELD => $status,
        ];
    }
}
