<?php

namespace OroCRM\Bundle\SalesBundle\Provider;

use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\EmailBundle\Entity\MailboxProcessor;
use Oro\Bundle\EmailBundle\Provider\MailboxProcessorInterface;

use OroCRM\Bundle\SalesBundle\Entity\LeadMailboxProcessor as ProcessorEntity;

class LeadMailboxProcessor implements MailboxProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function configureFromEntity(MailboxProcessor $processor)
    {
        // TODO: Implement configureFromEntity() method.
    }

    /**
     * {@inheritdoc}
     */
    public function process(Email $email)
    {
        // TODO: Implement process() method.
    }

    /**
     * Returns processor type.
     *
     * @return string
     */
    public function getType()
    {
        return ProcessorEntity::TYPE;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'orocrm.sales.mailbox_processor.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityFQCN()
    {
        return 'OroCRM\Bundle\SalesBundle\Entity\LeadMailboxProcessor';
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsFormType()
    {
        return 'orocrm_sales_mailbox_processor_lead';
    }
}
