<?php
namespace Oro\Bundle\FlexibleEntityBundle\EventListener;

use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleInterface;

use Oro\Bundle\FlexibleEntityBundle\Event\FilterFlexibleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Oro\Bundle\FlexibleEntityBundle\FlexibleEntityEvents;

/**
 * Aims to add all values / required values when create or load a new flexible :
 * - required : an empty (or default value) for each required attribute
 * - all : an empty (or default value) for each attribute
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class InitializeValuesListener implements EventSubscriberInterface
{

    /**
     * Specifies the list of events to listen
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            FlexibleEntityEvents::CREATE_FLEXIBLE => array('onCreateFlexibleEntity'),
        );
    }

    /**
     * Add values for each attribute
     * @param FilterFlexibleEvent $event
     */
    public function onCreateFlexibleEntity(FilterFlexibleEvent $event)
    {
        $flexible = $event->getEntity();
        $manager = $event->getManager();

        if ($flexible instanceof FlexibleInterface) {

            if ($manager->getFlexibleInitMode() !== 'empty') {

                // get initialization mode
                if ($manager->getFlexibleInitMode() === 'required_attributes') {
                    $onlyRequired = true;
                } else {
                    $onlyRequired = false;
                }

                // initialize expected values with default value if exists
                $attributes = $manager->getAttributeRepository()->findBy(
                    array('entityType' => $manager->getFlexibleName(), 'required' => $onlyRequired)
                );

                foreach ($attributes as $attribute) {
                    $value = $manager->createFlexibleValue();
                    $value->setAttribute($attribute);
                    if ($attribute->getDefaultValue() !== null) {
                        $value->setData($attribute->getDefaultValue());
                    }
                    $flexible->addValue($value);
                }
            }
        }
    }
}
