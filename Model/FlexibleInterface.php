<?php
namespace Oro\Bundle\FlexibleEntityBundle\Model;

/**
 * Flexible entity interface, allow to define a flexible entity without extends abstract class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
interface FlexibleInterface
{
    /**
     * Add value
     *
     * @param ValueInterface $value
     *
     * @return FlexibleInterface
     */
    public function addValue(ValueInterface $value);

    /**
     * Remove value
     *
     * @param ValueInterface $value
     */
    public function removeValue(ValueInterface $value);

    /**
     * Get values
     *
     * @return \ArrayAccess
     */
    public function getValues();

    /**
     * Get value related to attribute code
     *
     * @param string $attributeCode
     *
     * @return ValueInterface
     */
    public function getValue($attributeCode);
}
