<?php
namespace Oro\Bundle\FlexibleEntityBundle\Listener;

use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\HasDefaultValueInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Aims to add a default value behavior
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class HasDefaultValueListener implements EventSubscriber
{

    /**
     * Specifies the list of events to listen
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
            'preUpdate'
        );
    }

    /**
     * Before insert
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $this->defineDefaultValue($args);
    }

    /**
     * Before update
     *
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $this->defineDefaultValue($args);
    }

    /**
     * If Value is empty or null and has
     * @param LifecycleEventArgs $args
     */
    protected function defineDefaultValue(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        // check entity implements "has default value" behavior
        if ($entity instanceof HasDefaultValueInterface) {
            // check value has no data and attribute has default value
            if (!$entity->hasData() and !is_null($entity->getAttribute()->getDefaultValue())) {
                $defaultValue = $entity->getAttribute()->getDefaultValue();
                $entity->setData($defaultValue);
            }
        }
    }
}
