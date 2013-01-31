<?php
namespace Oro\Bundle\FlexibleEntityBundle\Model;

use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\TranslatableInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\ScopableInterface;

/**
 * Abstract entity value, independent of storage
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
abstract class AbstractValue implements ValueInterface, TranslatableInterface, ScopableInterface
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
     * @var mixed $data
     */
    protected $data;

    /**
     * @var string $locale
     */
    protected $locale;

    /**
     * @var string $scope
     */
    protected $scope;

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
     * Set id
     *
     * @param integer $id
     *
     * @return AbstractValue
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set data
     *
     * @param string $data
     *
     * @return AbstractValue
     */
     public function setData($data)
     {
         $this->data = $data;

         return $this;
     }

    /**
     * Get data
     *
     * @return string
     */
     public function getData()
     {
         return $this->data;
     }

     /**
      * Has data
      * @return boolean
      */
     public function hasData()
     {
         return !is_null($this->getData());
     }

    /**
     * Set attribute
     *
     * @param AbstractAttribute $attribute
     *
     * @return AbstractValue
     */
    public function setAttribute(AbstractAttribute $attribute = null)
    {
        $this->attribute = $attribute;

        return $this;
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
     * Get used locale
     * @return string $locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set used locale
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Get used scope
     * @return string $scope
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Set used scope
     * @param string $scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }

}
