<?php
namespace Oro\Bundle\SearchBundle\Query\Result\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Knp\Component\Pager\Event\ItemsEvent;

class PaginateResultSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return array(
            'knp_pager.items' => array('paginateResults', 1)
        );
    }

    public function paginateResults(ItemsEvent $event)
    {
        if (is_object($event->target) && $event->target instanceof \Oro\Bundle\SearchBundle\Query\Result) {
            /** @var $result \Oro\Bundle\SearchBundle\Query\Result  */
            $result = $event->target;
            $event->count = $result->getRecordsCount();
            $event->items = $result->toArray();
            $event->stopPropagation();
        }
    }
}
