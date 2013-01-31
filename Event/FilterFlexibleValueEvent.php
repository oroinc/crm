<?php
namespace Oro\Bundle\FlexibleEntityBundle\Event;

use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Oro\Bundle\FlexibleEntityBundle\Model\ValueInterface;

/**
 * Filter event allows to know the create flexible value
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class FilterFlexibleValueEvent extends AbstractFilterEvent
{
    /**
     * Flexible value
     * @var ValueInterface
     */
    protected $value;

    /**
     * Constructor
     *
     * @param FlexibleManager        $manager the manager
     * @param ValueInterface $value   the value
     */
    public function __construct(FlexibleManager $manager, ValueInterface $value)
    {
        parent::__construct($manager);
        $this->value = $value;
    }

    /**
     * @return ValueInterface
     */
    public function getValue()
    {
        return $this->value;
    }
}