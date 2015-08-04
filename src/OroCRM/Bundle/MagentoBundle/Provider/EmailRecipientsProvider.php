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

    /**
     * @param Registry $registry
     * @param RelatedEmailsProvider $relatedEmailsProvider
     */
    public function __construct(
        Registry $registry,
        RelatedEmailsProvider $relatedEmailsProvider
    ) {
        $this->registry = $registry;
        $this->relatedEmailsProvider = $relatedEmailsProvider;
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
                EmailRecipientsHelper::filterRecipients(
                    $args,
                    $this->relatedEmailsProvider->getEmails($customer, 2)
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
