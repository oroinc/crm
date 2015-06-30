<?php

namespace OroCRM\Bundle\MagentoBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\EmailBundle\Event\EmailRecipientsLoadEvent;
use Oro\Bundle\EmailBundle\Provider\EmailRecipientsHelper;
use Oro\Bundle\EmailBundle\Provider\RelatedEmailsProvider;

use OroCRM\Bundle\AccountBundle\Entity\Account;

class EmailRecipientsLoadListener
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
     * @param EmailRecipientsLoadEvent $event
     */
    public function onLoad(EmailRecipientsLoadEvent $event)
    {
        $limit = $event->getRemainingLimit();
        if (!$limit || !$event->getRelatedEntity() instanceof Account) {
            return;
        }

        $customers = $this->getCustomerRepository()->findBy(['account' => $event->getRelatedEntity()]);
        $emails = [];
        foreach ($customers as $customer) {
            $emails = array_merge($emails, $this->relatedEmailsProvider->getEmails($customer, 2));
        }

        $this->emailRecipientsHelper->addEmailsToContext($event, $emails);
    }

    /**
     * @return EntityRepository
     */
    protected function getCustomerRepository()
    {
        return $this->registry->getRepository('OroCRMMagentoBundle:Customer');
    }
}
