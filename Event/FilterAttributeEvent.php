<?php
namespace Oro\Bundle\FlexibleEntityBundle\Event;

use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeInterface;

/**
 * Filter event allows to know the create flexible attribute
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class FilterAttributeEvent extends AbstractFilterEvent
{
    /**
     * Flexible attribute
     * @var AttributeInterface
     */
    protected $attribute;

    /**
     * Constructor
     * @param FlexibleManager $manager
     * @param AttributeInterface $attribute
     */
    public function __construct(FlexibleManager $manager, AttributeInterface $attribute)
    {
        parent::__construct($manager);
        $this->attribute = $attribute;
    }

    /**
     * @return AttributeInterface
     */
    public function getAttribute()
    {
        return $this->attribute;
    }
}