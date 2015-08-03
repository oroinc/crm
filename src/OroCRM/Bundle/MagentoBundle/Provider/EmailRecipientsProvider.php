<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\EmailBundle\Provider\EmailRecipientsHelper;
use Oro\Bundle\EmailBundle\Provider\RelatedEmailsProvider;

use Oro\Bundle\EmailBundle\Model\EmailRecipientsProviderArgs;
use Oro\Bundle\EmailBundle\Provider\EmailRecipientsProviderInterface;

use OroCRM\Bundle\AccountBundle\Entity\Account;

class EmailRecipientsProvider implements EmailRecipientsProviderInterface
{
    /** @var Registry */
    protected $registry;

    /** @var RelatedEmailsProvider */
    protected $relatedEmailsProvider;

    /** @var EmailRecipientsHelper */
    protected $emailRecipientsHelper;

    /**
     * @param Registry $registry
     * @param RelatedEmailsProvider $relatedEmailsProvider
     * @param EmailRecipientsHelper $emailRecipientsHelper
     */
    public function __construct(
        Registry $registry,
        RelatedEmailsProvider $relatedEmailsProvider,
        EmailRecipientsHelper $emailRecipientsHelper
    ) {
        $this->registry = $registry;
        $this->relatedEmailsProvider = $relatedEmailsProvider;
        $this->emailRecipientsHelper = $emailRecipientsHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getRecipients(EmailRecipientsProviderArgs $args)
    {
        if (!$args->getRelatedEntity() instanceof Account) {
            return [];
        }

        $customers = $this->getCustomerRepository()->findBy(['account' => $args->getRelatedEntity()]);
        $emails = [];
        foreach ($customers as $customer) {
            $emails = array_merge(
                $emails,
                array_filter(
                    $this->relatedEmailsProvider->getEmails($customer, 2),
                    EmailRecipientsHelper::createRecipientsFilter($args)
                )
            );
        }

        return $emails;
    }

    /**
     * {@inheritdoc}
     */
    public function getSection()
    {
        return 'oro.email.autocomplete.contexts';
    }

    /**
     * @return EntityRepository
     */
    protected function getCustomerRepository()
    {
        return $this->registry->getRepository('OroCRMMagentoBundle:Customer');
    }
}
