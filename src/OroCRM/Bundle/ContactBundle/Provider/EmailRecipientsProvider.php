<?php

namespace OroCRM\Bundle\ContactBundle\Provider;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Oro\Bundle\EmailBundle\Provider\EmailRecipientsHelper;
use Oro\Bundle\LocaleBundle\DQL\DQLNameFormatter;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\EmailBundle\Model\EmailRecipientsProviderArgs;
use Oro\Bundle\EmailBundle\Provider\EmailRecipientsProviderInterface;

use OroCRM\Bundle\ContactBundle\Entity\Repository\ContactRepository;

class EmailRecipientsProvider implements EmailRecipientsProviderInterface
{
    /** @var Registry */
    protected $registry;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var EmailRecipientsHelper */
    protected $emailRecipientsHelper;

    /** @var DQLNameFormatter */
    protected $nameFormatter;

    /**
     * @param Registry $registry
     * @param AclHelper $aclHelper
     * @param EmailRecipientsHelper $emailRecipientsHelper
     * @param DQLNameFormatter $nameFormatter
     */
    public function __construct(
        Registry $registry,
        AclHelper $aclHelper,
        EmailRecipientsHelper $emailRecipientsHelper,
        DQLNameFormatter $nameFormatter
    ) {
        $this->registry = $registry;
        $this->aclHelper = $aclHelper;
        $this->emailRecipientsHelper = $emailRecipientsHelper;
        $this->nameFormatter = $nameFormatter;
    }

    /**
     * {@inheritdoc}
     */
    public function getRecipients(EmailRecipientsProviderArgs $args)
    {
        $fullNameQueryPart = $this->nameFormatter->getFormattedNameDQL(
            'c',
            'OroCRM\Bundle\ContactBundle\Entity\Contact'
        );

        return $this->getContactRepository()->getEmails(
            $this->aclHelper,
            $fullNameQueryPart,
            $args->getExcludedEmails(),
            $args->getQuery(),
            $args->getLimit()
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
