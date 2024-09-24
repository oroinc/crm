<?php

namespace Oro\Bundle\CaseBundle\Provider;

use Oro\Bundle\CaseBundle\Entity\CaseMailboxProcessSettings;
use Oro\Bundle\CaseBundle\Form\Type\CaseMailboxProcessSettingsType;
use Oro\Bundle\EmailBundle\Entity\Mailbox;
use Oro\Bundle\EmailBundle\Mailbox\MailboxProcessProviderInterface;

/**
 * Registers convert to case mailbox email process.
 * Actual implementation of this process can be found in processes.yml of this bundle.
 */
class CaseMailboxProcessProvider implements MailboxProcessProviderInterface
{
    const PROCESS_DEFINITION_NAME = 'convert_mailbox_email_to_case';

    #[\Override]
    public function getSettingsEntityFQCN()
    {
        return CaseMailboxProcessSettings::class;
    }

    #[\Override]
    public function getSettingsFormType()
    {
        return CaseMailboxProcessSettingsType::class;
    }

    #[\Override]
    public function getLabel()
    {
        return 'oro.case.mailbox.process.case.label';
    }

    #[\Override]
    public function isEnabled(Mailbox $mailbox = null)
    {
        return true;
    }

    #[\Override]
    public function getProcessDefinitionName()
    {
        return self::PROCESS_DEFINITION_NAME;
    }
}
