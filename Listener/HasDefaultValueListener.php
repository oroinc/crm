<?php
namespace Oro\Bundle\FlexibleEntityBundle\Listener;

use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\HasDataInterface;
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
     * @return multitype:string
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

        // check value has default value
        if ($entity instanceof HasDataInterface && !$entity->hasData()) {
            $defaultValue = $entity->getAttribute()->getDefaultValue();
            if ($defaultValue !== null) {
                $entity->setData($defaultValue);
            }
        }
    }
}
