<?php

namespace OroCRM\Bundle\CaseBundle\Provider;

use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\EmailBundle\Entity\EmailUser;
use Oro\Bundle\EmailBundle\Entity\MailboxProcessorSettings;
use Oro\Bundle\EmailBundle\Provider\MailboxProcessorInterface;

use OroCRM\Bundle\CaseBundle\Entity\CaseMailboxProcessorSettings as ProcessorEntity;

class CaseMailboxProcessor implements MailboxProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function configureFromEntity(MailboxProcessorSettings $processor)
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    public function process(EmailUser $emailUser)
    {
        //
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
     * Returns label of processor type.
     *
     * @return string
     */
    public function getLabel()
    {
        return 'orocrm.case.mailbox_processor.label';
    }

    /**
     * Returns fully qualified class name of settings entity for this processor.
     *
     * @return string
     */
    public function getEntityFQCN()
    {
        return 'OroCRM\Bundle\CaseBundle\Entity\CaseMailboxProcessorSettings';
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsFormType()
    {
        return 'orocrm_case_mailbox_processor';
    }
}
