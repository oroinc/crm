<?php
namespace Oro\Bundle\FlexibleEntityBundle\AttributeType;

/**
 * The attribute type interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
interface AttributeTypeInterface
{
    /**
     * Get name
     * 
     * @return string
     */
    public function getName();

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions();

    /**
     * Initialize
     *
     * @param string $name    the name
     * @param array  $options the options
     */
    public function initialize($name, $options = array());
}
