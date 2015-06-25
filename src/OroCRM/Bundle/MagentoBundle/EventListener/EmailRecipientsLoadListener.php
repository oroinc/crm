<?php

namespace OroCRM\Bundle\MagentoBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\EmailBundle\Event\EmailRecipientsLoadEvent;
use Oro\Bundle\EmailBundle\Provider\RelatedEmailsProvider;
use OroCRM\Bundle\AccountBundle\Entity\Account;

class EmailRecipientsLoadListener
{
    /** @var Registry */
    protected $registry;

    /** @var RelatedEmailsProvider */
    protected $relatedEmailsProvider;

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param Registry $registry
     * @param RelatedEmailsProvider $relatedEmailsProvider
     * @param TranslatorInterface $translator
     */
    public function __construct(
        Registry $registry,
        RelatedEmailsProvider $relatedEmailsProvider,
        TranslatorInterface $translator
    ) {
        $this->registry = $registry;
        $this->relatedEmailsProvider = $relatedEmailsProvider;
        $this->translator = $translator;
    }

    /**
     * @param EmailRecipientsLoadEvent $event
     */
    public function onLoad(EmailRecipientsLoadEvent $event)
    {
        $query = $event->getQuery();
        $limit = $event->getLimit() - count($event->getResults());

        if (!$limit || !$event->getRelatedEntity() instanceof Account) {
            return;
        }

        $customers = $this->getCustomerRepository()->findByAccount($event->getRelatedEntity());
        $emails = [];
        foreach ($customers as $customer) {
            $emails = array_merge($emails, $this->relatedEmailsProvider->getEmails($customer, 2));
        }

        $excludedEmails = $event->getEmails();
        $filteredEmails = array_filter($emails, function ($email) use ($query, $excludedEmails) {
            return !in_array($email, $excludedEmails) && stripos($email, $query) !== false;
        });
        if (!$filteredEmails) {
            return;
        }

        $id = $this->translator->trans('oro.email.autocomplete.contexts');
        $resultsId = null;
        $results = $event->getResults();
        foreach ($results as $recordId => $record) {
            if ($record['text'] === $id) {
                $resultsId = $recordId;

                break;
            }
        }

        $children = $this->createResultFromEmails(array_splice($filteredEmails, 0, $limit));
        if ($resultsId !== null) {
            $results[$resultsId]['children'] = array_merge($results[$resultsId]['children'], $children);
        } else {
            $results = array_merge(
                $results,
                [
                    [
                        'text'     => $id,
                        'children' => $children,
                    ],
                ]
            );
        }

        $event->setResults($results);
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
     * @return EntityRepository
     */
    protected function getCustomerRepository()
    {
        return $this->registry->getRepository('OroCRMMagentoBundle:Customer');
    }
}
