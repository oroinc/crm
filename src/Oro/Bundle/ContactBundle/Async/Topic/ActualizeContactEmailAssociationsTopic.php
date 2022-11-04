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
    public static function getName(): string
    {
        return 'oro_contact.actualize_contact_email_associations';
    }

    public static function getDescription(): string
    {
        return 'Actualizes EmailAddress records.';
    }

    public function getDefaultPriority(string $queueName): string
    {
        return MessagePriority::VERY_LOW;
    }

    public function configureMessageBody(OptionsResolver $resolver): void
    {
    }
}
