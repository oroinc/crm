<?php
namespace Oro\Bundle\FlexibleEntityBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeInterface;

/**
 * Filter event allows to know the create flexible attribute
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class FilterAttributeEvent extends Event
{
    /**
     * Flexible attribute
     * @var AttributeInterface
     */
    protected $attribute;

    /**
     * Constructor
     * @param AttributeInterface $attribute
     */
    public function __construct(AttributeInterface $attribute)
    {
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