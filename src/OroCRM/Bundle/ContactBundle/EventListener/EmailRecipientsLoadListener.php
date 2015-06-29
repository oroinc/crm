<?php

namespace OroCRM\Bundle\ContactBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\EmailBundle\Event\EmailRecipientsLoadEvent;
use Oro\Bundle\EmailBundle\Provider\EmailRecipientsHelper;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use OroCRM\Bundle\ContactBundle\Entity\Repository\ContactRepository;

class EmailRecipientsLoadListener
{
    /** @var Registry */
    protected $registry;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var EmailRecipientsHelper */
    protected $emailRecipientsHelper;

    /**
     * @param Registry $registry
     * @param AclHelper $aclHelper
     * @param TranslatorInterface $translator
     * @param EmailRecipientsHelper $emailRecipientsHelper
     */
    public function __construct(
        Registry $registry,
        AclHelper $aclHelper,
        TranslatorInterface $translator,
        EmailRecipientsHelper $emailRecipientsHelper
    ) {
        $this->registry = $registry;
        $this->aclHelper = $aclHelper;
        $this->translator = $translator;
        $this->emailRecipientsHelper = $emailRecipientsHelper;
    }

    /**
     * @param EmailRecipientsLoadEvent $event
     */
    public function onLoad(EmailRecipientsLoadEvent $event)
    {
        $query = $event->getQuery();
        $limit = $event->getRemainingLimit();

        if (!$limit) {
            return;
        }

        $contactEmails = $this->getContactRepository()
            ->getEmails($this->aclHelper, $event->getEmails(), $query, $limit);

        if (!$contactEmails) {
            return;
        }

        $event->setResults(array_merge(
            $event->getResults(),
            [
                [
                    'text'     => $this->translator->trans('orocrm.contact.entity_plural_label'),
                    'children' => $this->emailRecipientsHelper->createResultFromEmails($contactEmails),
                ],
            ]
        ));
    }

    /**
     * @return ContactRepository
     */
    protected function getContactRepository()
    {
        return $this->registry->getRepository('OroCRMContactBundle:Contact');
    }
}
