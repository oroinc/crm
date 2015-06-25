<?php

namespace OroCRM\Bundle\ContactBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\EmailBundle\Event\EmailRecipientsLoadEvent;
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

    /**
     * @param Registry $registry
     * @param AclHelper $aclHelper
     * @param TranslatorInterface $translator
     */
    public function __construct(Registry $registry, AclHelper $aclHelper, TranslatorInterface $translator)
    {
        $this->registry = $registry;
        $this->aclHelper = $aclHelper;
        $this->translator = $translator;
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
                    'children' => $this->createResultFromEmails($contactEmails),
                ],
            ]
        ));
    }

    /**
     * @param array $emails
     *
     * @return array
     */
    protected function createResultFromEmails(array $emails)
    {
        $result = [];
        foreach ($emails as $email => $name) {
            $result[] = [
                'id'   => $email,
                'text' => $name,
            ];
        }

        return $result;
    }

    /**
     * @return ContactRepository
     */
    protected function getContactRepository()
    {
        return $this->registry->getRepository('OroCRMContactBundle:Contact');
    }
}
