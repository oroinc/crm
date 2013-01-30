<?php
namespace Oro\Bundle\FlexibleEntityBundle\Model;

/**
 * Flexible attribute interface
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
interface AttributeInterface
{
    /**
     * Get unique code
     *
     * @return string
     */
    public function getCode();
}
