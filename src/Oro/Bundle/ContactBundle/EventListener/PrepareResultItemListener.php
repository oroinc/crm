<?php

namespace Oro\Bundle\ContactBundle\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Formatter\ContactNameFormatter;
use Oro\Bundle\SearchBundle\Event\PrepareResultItemEvent;

/**
 * Fills a title for found Contact entity when the title is empty.
 */
class PrepareResultItemListener
{
    private ContactNameFormatter $nameFormatter;
    private ManagerRegistry $doctrine;

    public function __construct(ContactNameFormatter $nameFormatter, ManagerRegistry $doctrine)
    {
        $this->nameFormatter = $nameFormatter;
        $this->doctrine = $doctrine;
    }

    public function prepareResultItem(PrepareResultItemEvent $event): void
    {
        $resultItem = $event->getResultItem();
        if ($resultItem->getEntityName() !== Contact::class || trim($resultItem->getRecordTitle())) {
            return;
        }

        /** @var Contact $entity */
        $entity = $this->doctrine->getManagerForClass(Contact::class)
            ->find(Contact::class, $resultItem->getId());

        $resultItem->setRecordTitle($this->nameFormatter->format($entity));
    }
}
