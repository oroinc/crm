<?php
namespace Oro\Bundle\FlexibleEntityBundle\Event;

use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleEntityInterface;

/**
 * Filter event allows to know the create flexible value
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class FilterFlexibleEntityEvent extends AbstractFilterEvent
{
    /**
     * Flexible entity
     * @var FlexibleEntityInterface
     */
    protected $entity;

    /**
     * Constructor
     * @param FlexibleManager $manager
     * @param FlexibleEntityInterface $entity
     */
    public function __construct(FlexibleManager $manager, FlexibleEntityInterface $entity)
    {
        parent::__construct($manager);
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