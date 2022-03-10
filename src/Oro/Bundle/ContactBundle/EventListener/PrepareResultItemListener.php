<?php

namespace Oro\Bundle\ContactBundle\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Formatter\ContactNameFormatter;
use Oro\Bundle\SearchBundle\Event\PrepareResultItemEvent;

/**
 * Fills a title for found Contact entity when the title is empty.
 *
 * @deprecated deprecated since 5.0
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
        // entity title is now selected from the search index
    }
}
