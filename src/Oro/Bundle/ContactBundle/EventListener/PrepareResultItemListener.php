<?php

namespace Oro\Bundle\ContactBundle\EventListener;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Formatter\ContactNameFormatter;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SearchBundle\Event\PrepareResultItemEvent;

class PrepareResultItemListener
{
    /** @var ContactNameFormatter */
    protected $nameFormatter;

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * PrepareResultItemListener constructor.
     */
    public function __construct(ContactNameFormatter $nameFormatter, DoctrineHelper $doctrineHelper)
    {
        $this->nameFormatter = $nameFormatter;
        $this->doctrineHelper = $doctrineHelper;
    }

    public function prepareEmailItemDataEvent(PrepareResultItemEvent $event)
    {
        if (trim($event->getResultItem()->getRecordTitle()) ||
            $event->getResultItem()->getEntityName() !== 'Oro\Bundle\ContactBundle\Entity\Contact'
        ) {
            return;
        }

        $resultItem = $event->getResultItem();

        /** @var Contact $entity */
        $entity = $this
            ->doctrineHelper
            ->getEntityRepository($resultItem->getEntityName())
            ->find($resultItem->getId());

        $resultItem->setRecordTitle($this->nameFormatter->format($entity));
    }
}
