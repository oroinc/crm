<?php

namespace Oro\Bundle\ContactBundle\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\EmailBundle\Model\EmailRecipientsProviderArgs;
use Oro\Bundle\EmailBundle\Provider\EmailRecipientsHelper;
use Oro\Bundle\EmailBundle\Provider\EmailRecipientsProviderInterface;

/**
 * Provider for email recipient list based on Contact.
 */
class EmailRecipientsProvider implements EmailRecipientsProviderInterface
{
    private ManagerRegistry $doctrine;
    private EmailRecipientsHelper $emailRecipientsHelper;

    public function __construct(ManagerRegistry $doctrine, EmailRecipientsHelper $emailRecipientsHelper)
    {
        $this->doctrine = $doctrine;
        $this->emailRecipientsHelper = $emailRecipientsHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getRecipients(EmailRecipientsProviderArgs $args)
    {
        return $this->emailRecipientsHelper->getRecipients(
            $args,
            $this->doctrine->getRepository(Contact::class),
            'c',
            Contact::class
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSection(): string
    {
        return 'oro.contact.entity_plural_label';
    }
}
