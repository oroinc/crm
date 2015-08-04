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

        $primaryEmailsQb = $this->getContactRepository()
            ->getPrimaryEmailsQb($fullNameQueryPart, $args->getExcludedEmails(), $args->getQuery())
            ->setMaxResults($args->getLimit());

        $primaryEmailsResult = $this->aclHelper->apply($primaryEmailsQb)->getResult();
        $emails = $this->emailsFromResult($primaryEmailsResult);

        $limit = $args->getLimit() - count($emails);

        if ($limit > 0) {
            $excludedEmails = array_merge($args->getExcludedEmails(), array_keys($emails));
            $secondaryEmailsQb = $this->getContactRepository()
                ->getSecondaryEmailsQb($fullNameQueryPart, $excludedEmails, $args->getQuery())
                ->setMaxResults($limit);

            $secondaryEmailsResult = $this->aclHelper->apply($secondaryEmailsQb)->getResult();
            $emails = array_merge($emails, $this->emailsFromResult($secondaryEmailsResult));
        }

        return $emails;
    }

    /**
     * {@inheritdoc}
     */
    public function getSection()
    {
        return 'orocrm.contact.entity_plural_label';
    }

    /**
     * @param array $result
     *
     * @return array
     */
    protected function emailsFromResult(array $result)
    {
        $emails = [];
        foreach ($result as $row) {
            $emails[$row['email']] = sprintf('%s <%s>', $row['name'], $row['email']);
        }

        return $emails;
    }

    /**
     * @return ContactRepository
     */
    protected function getContactRepository()
    {
        return $this->registry->getRepository('OroCRMContactBundle:Contact');
    }
}
