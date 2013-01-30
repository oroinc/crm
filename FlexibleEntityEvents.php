<?php
namespace Oro\Bundle\FlexibleEntityBundle;

/**
 * Flexible events
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
final class FlexibleEntityEvents
{

    /**
     * This event is thrown each time a flexible attribute is created in the system.
     *
     * The event listener receives an
     * Oro\Bundle\FlexibleEntityBundle\Event\FilterFlexibleEvent instance.
     *
     * @var string
     */
    const CREATE_ATTRIBUTE          = 'oro_flexible.create_attribute';

    /**
     * This event is thrown each time a flexible entity is created in the system.
     *
     * The event listener receives an
     * Oro\Bundle\FlexibleEntityBundle\Event\FilterFlexibleEvent instance.
     *
     * @var string
     */
    const CREATE_FLEXIBLE_ENTITY    = 'oro_flexible.create_flexible_entity';

    /**
     * This event is thrown each time a flexible value is created in the system.
     *
     * The event listener receives an
     * Oro\Bundle\FlexibleEntityBundle\Event\FilterFlexibleEvent instance.
     *
     * @var string
     */
    const CREATE_FLEXIBLE_VALUE     = 'oro_flexible.create_flexible_value';
}