<?php

namespace OroCRM\Bundle\ContactBundle\Provider;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Oro\Bundle\EmailBundle\Model\EmailRecipientsProviderArgs;
use Oro\Bundle\EmailBundle\Provider\EmailRecipientsProviderInterface;
use Oro\Bundle\EmailBundle\Provider\EmailRecipientsHelper;

use OroCRM\Bundle\ContactBundle\Entity\Repository\ContactRepository;

class EmailRecipientsProvider implements EmailRecipientsProviderInterface
{
    /** @var Registry */
    protected $registry;

    /** @var EmailRecipientsHelper */
    protected $emailRecipientsHelper;

    /**
     * @param Registry $registry
     * @param EmailRecipientsHelper $emailRecipientsHelper
     */
    public function __construct(
        Registry $registry,
        EmailRecipientsHelper $emailRecipientsHelper
    ) {
        $this->registry = $registry;
        $this->emailRecipientsHelper = $emailRecipientsHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getRecipients(EmailRecipientsProviderArgs $args)
    {
        return $this->emailRecipientsHelper->getRecipients(
            $args,
            $this->getContactRepository(),
            'c',
            'OroCRM\Bundle\ContactBundle\Entity\Contact'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getSection()
    {
        return 'orocrm.contact.entity_plural_label';
    }

    /**
     * @return ContactRepository
     */
    protected function getContactRepository()
    {
        return $this->registry->getRepository('OroCRMContactBundle:Contact');
    }
}
