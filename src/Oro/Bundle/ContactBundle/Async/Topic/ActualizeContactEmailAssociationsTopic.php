<?php

namespace Oro\Bundle\ContactBundle\Async\Topic;

use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Topic\AbstractTopic;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A topic to actualize EmailAddress records
 */
class ActualizeContactEmailAssociationsTopic extends AbstractTopic
{
    #[\Override]
    public static function getName(): string
    {
        return 'oro_contact.actualize_contact_email_associations';
    }

    #[\Override]
    public static function getDescription(): string
    {
        return 'Actualizes EmailAddress records.';
    }

    #[\Override]
    public function getDefaultPriority(string $queueName): string
    {
        return MessagePriority::VERY_LOW;
    }

    #[\Override]
    public function configureMessageBody(OptionsResolver $resolver): void
    {
    }
}
