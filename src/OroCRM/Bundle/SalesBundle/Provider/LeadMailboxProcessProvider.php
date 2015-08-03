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
     * {@inheritdoc}
     */
    public function getSettingsFormType()
    {
        return 'orocrm_sales_lead_mailbox_process_settings';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'orocrm.sales.mailbox.process.lead.label';
    }
}
