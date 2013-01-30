<?php
namespace Oro\Bundle\FlexibleEntityBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleEntityInterface;

/**
 * Filter event allows to know the create flexible value
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class FilterFlexibleEntityEvent extends Event
{
    /**
     * Flexible entity
     * @var FlexibleEntityInterface
     */
    protected $entity;

    /**
     *
     * @param FlexibleEntityInterface $flexible
     */
    public function __construct(FlexibleEntityInterface $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return FlexibleEntityInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }
}