<?php

namespace OroCRM\Bundle\CaseBundle\Provider;

use Oro\Bundle\EmailBundle\Mailbox\MailboxProcessProviderInterface;

/**
 * Class CaseMailboxProcessProvider
 *
 * Registers convert to case mailbox email process.
 * Actual implementation of this process can be found in process.yml of this bundle.
 *
 * @package OroCRM\Bundle\CaseBundle\Provider
 */
class CaseMailboxProcessProvider implements MailboxProcessProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSettingsEntityFQCN()
    {
        return 'OroCRM\Bundle\CaseBundle\Entity\CaseMailboxProcessSettings';
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsFormType()
    {
        return 'orocrm_case_mailbox_process_settings';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'orocrm.case.mailbox.process.case.label';
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return true;
    }
}
