<?php
namespace Oro\Bundle\FlexibleEntityBundle\Model;

use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\FlexibleValueInterface;

/**
 * Abstract entity value, independent of storage
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
abstract class AbstractFlexibleValue implements FlexibleValueInterface
{

    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var EntityAttribute $attribute
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
     * Set data
     *
     * @param string $data
     *
     * @return AbstractFlexibleValue
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
     * @param EntityAttribute $attribute
     *
     * @return AbstractFlexibleValue
     */
    public function setAttribute(AbstractAttribute $attribute = null)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Get attribute
     *
     * @return AbstractFlexibleValue
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
