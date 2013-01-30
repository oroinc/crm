<?php
namespace Oro\Bundle\FlexibleEntityBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;

/**
 * Filter event allows to know the create flexible value
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class FilterFlexibleValueEvent extends Event
{
    /**
     * Flexible value
     * @var FlexibleValueInterface
     */
    protected $value;

    /**
     * Constructor
     * @param FlexibleValueInterface $value
     */
    public function __construct(FlexibleValueInterface $value)
    {
        $this->value = $value;
    }

    /**
     * @return FlexibleValueInterface
     */
    public function getValue()
    {
        return $this->value;
    }
}