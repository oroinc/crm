<?php

namespace OroCRM\Bundle\ContactBundle\EventListener;

use Oro\Bundle\SearchBundle\Event\PrepareResultItemEvent;

use OroCRM\Bundle\ContactBundle\Formatter\ContactNameFormatter;

class PrepareResultItemListener
{
    /** @var ContactNameFormatter */
    protected $nameFormatter;

    /**
     * @param ContactNameFormatter $nameFormatter
     */
    public function __construct(ContactNameFormatter $nameFormatter)
    {
        $this->nameFormatter = $nameFormatter;
    }

    /**
     * @param PrepareResultItemEvent $event
     */
    public function prepareEmailItemDataEvent(PrepareResultItemEvent $event)
    {
        if (trim($event->getResultItem()->getRecordTitle()) ||
            $event->getResultItem()->getEntityName() !== 'OroCRM\Bundle\ContactBundle\Entity\Contact'
        ) {
            return;
        }

        $resultItem = $event->getResultItem();
        $resultItem->setRecordTitle($this->nameFormatter->format($resultItem->getEntity()));
    }
}
