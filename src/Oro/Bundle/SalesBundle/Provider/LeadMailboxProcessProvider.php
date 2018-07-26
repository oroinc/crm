<?php

namespace Oro\Bundle\SalesBundle\Provider;

use Oro\Bundle\EmailBundle\Entity\Mailbox;
use Oro\Bundle\EmailBundle\Mailbox\MailboxProcessProviderInterface;
use Oro\Bundle\SalesBundle\Entity\LeadMailboxProcessSettings;
use Oro\Bundle\SalesBundle\Form\Type\LeadMailboxProcessSettingsType;

/**
 * Registers convert to lead mailbox email process.
 * Actual implementation of this process can be found in processes.yml of this bundle.
 */
class LeadMailboxProcessProvider implements MailboxProcessProviderInterface
{
    const PROCESS_DEFINITION_NAME = 'convert_mailbox_email_to_lead';

    /**
     * {@inheritdoc}
     */
    public function getSettingsEntityFQCN()
    {
        return LeadMailboxProcessSettings::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsFormType()
    {
        return LeadMailboxProcessSettingsType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'oro.sales.mailbox.process.lead.label';
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(Mailbox $mailbox = null)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessDefinitionName()
    {
        return self::PROCESS_DEFINITION_NAME;
    }
}
