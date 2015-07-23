<?php

namespace OroCRM\Bundle\SalesBundle\Provider;

use Oro\Bundle\EmailBundle\Mailbox\MailboxProcessProviderInterface;

class LeadMailboxProcessProvider implements MailboxProcessProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getSettingsEntityFQCN()
    {
        return 'OroCRM\Bundle\SalesBundle\Entity\LeadMailboxProcessSettings';
    }

    /**
     * Returns form type used for settings entity used by this process.
     *
     * @return string
     */
    public function getSettingsFormType()
    {
        return 'orocrm_sales_lead_mailbox_process_settings';
    }

    /**
     * Returns id for translation which is used as label for this process type.
     *
     * @return string
     */
    public function getLabel()
    {
        return 'orocrm.sales.mailbox.process.lead.label';
    }
}
