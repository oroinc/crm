<?php
namespace Oro\Bundle\FlexibleEntityBundle\Entity\Mapping;

use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\FlexibleValueInterface;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractFlexible;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractFlexibleValue;
use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\TranslatableContainerInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\ScopableContainerInterface;

/**
 * Base Doctrine ORM entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
abstract class AbstractEntityFlexible extends AbstractFlexible implements TranslatableContainerInterface, ScopableContainerInterface
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var datetime $created
     *
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @var datetime $updated
     *
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     * Not persisted but allow to force locale for values
     * @var string $locale
     */
    protected $locale;

    /**
     * Not persisted but allow to force scope for values
     * @var string $scope
     */
    protected $scope;

    /**
     * @var Value
     *
     * @ORM\OneToMany(targetEntity="AbstractEntityFlexibleValue", mappedBy="entity", cascade={"persist", "remove"})
     */
    protected $values;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->values = new ArrayCollection();
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
     *
     * @param string $locale
     *
     * @return AbstractFlexible
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
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
     *
     * @param string $scope
     *
     * @return AbstractFlexible
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Add value, override to deal with relation owner side
     *
     * @param FlexibleValueInterface $value
     *
     * @return AbstractEntityFlexible
     */
    public function addValue(FlexibleValueInterface $value)
    {
        $this->values[] = $value;
        $value->setEntity($this);

        return $this;
    }

    /**
     * Remove value
     *
     * @param FlexibleValueInterface $value
     */
    public function removeValue(FlexibleValueInterface $value)
    {
        $this->values->removeElement($value);
    }

    /**
     * Get values
     *
     * @return \ArrayAccess
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Get value related to attribute code
     *
     * @param string $attributeCode
     *
     * @return FlexibleValueInterface
     */
    public function getValue($attributeCode)
    {
        $locale = $this->getLocale();
        $scope = $this->getScope();
        $values = $this->getValues()->filter(function($value) use ($attributeCode, $locale, $scope) {
            // related value to asked attribute
            if ($value->getAttribute()->getCode() == $attributeCode) {
                // return relevant translated value if translatable
                if ($value->getAttribute()->getTranslatable() and $value->getLocale() == $locale) {
                    // check also scope if scopable
                    if ($value->getAttribute()->getScopable() and $value->getScope() == $scope) {
                        return true;
                    } else if (!$value->getAttribute()->getScopable()) {
                        return true;
                    }
                // return the value if not translatable
                } else if (!$value->getAttribute()->getTranslatable()) {
                    return true;
                }
            }

            return false;
        });
        $value = $values->first();

        return $value;
    }

    /**
     * Get value data (string, number, etc) related to attribute code
     *
     * @param string $attributeCode
     *
     * @return mixed|NULL
     */
    public function getValueData($attributeCode)
    {
        $value = $this->getValue($attributeCode);

        return ($value) ? $value->getData() : null;
    }

    /**
     * Check if a field or attribute exists
     *
     * @param string $name
     *
     * @return boolean
     */
    public function __isset($name)
    {
        // to authorize call to dynamic __get by twig, should be filter on existing attributes
        // cf http://twig.sensiolabs.org/doc/recipes.html#using-dynamic-object-properties
        return true;
    }

    /**
     * Get value data by attribute code
     *
     * @param string $attCode
     *
     * @return boolean|NULL
     */
    public function __get($attCode)
    {
        // call existing getAttCode method
        $methodName = "get{$attCode}";
        if (method_exists($this, $methodName)) {
            return $this->$methodName();
        // dynamic call to get value data
        } else {
            return $this->getValueData($attCode);
        }
    }

}
