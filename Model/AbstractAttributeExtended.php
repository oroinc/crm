<?php
namespace Oro\Bundle\FlexibleEntityBundle\Model;

/**
 * Abstract entity attribute, independent of storage
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
abstract class AbstractAttributeExtended
{

    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var AbstractAttribute $attribute
     */
    protected $attribute;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get attribute
     *
     * @return AbstractAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Set attribute
     *
     * @param AbstractAttribute $attribute
     *
     * @return AbstractAttributeExtended
     */
    public function setAttribute(AbstractAttribute $attribute = null)
    {
        $this->attribute = $attribute;

        return $this;
    }

}
