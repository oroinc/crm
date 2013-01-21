<?php
namespace Oro\Bundle\FlexibleEntityBundle\Model\Behavior;

/**
 * Has data interface provides a method hasData() to know if a value is set or not
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
interface HasDataInterface
{
    /**
     * Predicate to know if a value is set
     * @return boolean
     */
    public function hasData();
}
