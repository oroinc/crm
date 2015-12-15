<?php

namespace OroCRM\Bundle\MagentoBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Akeneo\Bundle\BatchBundle\Event\EventInterface;
use Akeneo\Bundle\BatchBundle\Event\StepExecutionEvent;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class OrderImportPostProcessSubscriber implements EventSubscriberInterface
{
    const LAST_STEP = 'mage_order_import_post_process_load_order_info';

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            EventInterface::STEP_EXECUTION_SUCCEEDED => 'stepExecutionSucceeded'
        ];
    }

    /**
     * @param StepExecutionEvent $event
     */
    public function stepExecutionSucceeded(StepExecutionEvent $event)
    {
        if (self::LAST_STEP !== $event->getStepExecution()->getStepName()) {
            return;
        }

        $em = $this->doctrineHelper->getEntityManager('OroCRMMagentoBundle:Order');
        $em->clear();
    }
}
