<?php
namespace Oro\Bundle\FlexibleEntityBundle\EventListener;

use Oro\Bundle\FlexibleEntityBundle\Event\FilterFlexibleEntityEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Oro\Bundle\FlexibleEntityBundle\FlexibleEntityEvents;

/**
 * Aims to add all default values when create a flexible
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class CreationValuesListener implements EventSubscriberInterface
{

    /**
     * Specifies the list of events to listen
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            FlexibleEntityEvents::CREATE_FLEXIBLE_ENTITY => array('onCreateFlexibleEntity'),
        );
    }

    /**
     * Add values for each attribute
     * @param FilterFlexibleEntityEvent $event
     */
    public function onCreateFlexibleEntity(FilterFlexibleEntityEvent $event)
    {
        $flexible = $event->getEntity();
        // TODO: chek it match interface

        //die('here evenent '.get_class($flexible));

        /*
        $values = array();
        $attributes = $this->getAttributeRepository()->findBy(array('entityType' => $this->getEntityName()));

        foreach ($attributes as $attribute) {
            $value = $this->createEntityValue();
            $value->setAttribute($attribute);
            $object->addValue($value);
        }*/

    }

}
