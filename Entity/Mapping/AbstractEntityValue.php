<?php
namespace Oro\Bundle\FlexibleEntityBundle\Entity\Mapping;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractFlexible;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractValue;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Base Doctrine ORM entity attribute value
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT  MIT
 *
 */
abstract class AbstractEntityValue extends AbstractValue
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
     * @var Attribute $attribute
     *
     * @ORM\ManyToOne(targetEntity="AbstractEntityAttribute")
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $attribute;

    /**
     * @var Entity $entity
     *
     * @ORM\ManyToOne(targetEntity="AbstractEntityFlexible", inversedBy="values")
     */
    protected $entity;

    /**
     * Locale code
     * @var string $locale
     *
     * @ORM\Column(name="locale_code", type="string", length=5, nullable=false)
     */
    protected $locale;

    /**
     * Locale code
     * @var string $scope
     *
     * @ORM\Column(name="scope_code", type="string", length=20, nullable=true)
     */
    protected $scope;

    /**
     * Currency code
     * @var string $currency
     *
     * @ORM\Column(name="currency_code", type="string", length=5, nullable=true)
     */
    protected $currency;

    /**
     * Unit code
     * @var string $unit
     *
     * @ORM\Column(name="unit_code", type="string", length=5, nullable=true)
     */
    protected $unit;

    /**
     * Store varchar value
     * @var string $varchar
     *
     * @ORM\Column(name="value_string", type="string", length=255, nullable=true)
     */
    protected $varchar;

    /**
     * Store int value
     * @var integer $integer
     *
     * @ORM\Column(name="value_integer", type="integer", nullable=true)
     */
    protected $integer;

    /**
     * Store decimal value
     * @var double $decimal
     *
     * @ORM\Column(name="value_decimal", type="decimal", nullable=true)
     */
    protected $decimal;

    /**
     * Store text value
     * @var string $text
     *
     * @ORM\Column(name="value_text", type="text", nullable=true)
     */
    protected $text;

    /**
     * Store date value
     * @var date $date
     *
     * @ORM\Column(name="value_date", type="date", nullable=true)
     */
    protected $date;

    /**
     * Store datetime value
     * @var string $datetime
     *
     * @ORM\Column(name="value_datetime", type="datetime", nullable=true)
     */
    protected $datetime;

    /**
     * Store options values
     * @var options ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AbstractEntityAttributeOption")
     * @ORM\JoinTable(name="oroflexibleentity_values_options",
     *      joinColumns={@ORM\JoinColumn(name="value_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="option_id", referencedColumnName="id")}
     * )
     */
    protected $options;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->options = new ArrayCollection();
    }

    /**
     * Set entity
     *
     * @param AbstractFlexible $entity
     *
     * @return EntityAttributeValue
     */
    public function setEntity(AbstractFlexible $entity = null)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Set data
     *
     * @param mixed $data
     *
     * @return EntityAttributeValue
     */
    public function setData($data)
    {
        parent::setData($data);
        $backend = $this->attribute->getBackendType();
        $this->$backend = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return string
     */
    public function getData()
    {
        $backend = $this->attribute->getBackendType();
        $data    = $this->$backend;
        // deal with one to many or many to many backend
        if ($data instanceof \Doctrine\ORM\PersistentCollection) {
            // one to many
            if (count($data) <= 1) {
                return $data->current();
            // many to many
            } else {
                $items = array();
                foreach ($data as $item) {
                    $items[]= $item->__toString();
                }

                return implode(', ', $items);
            }
        }

        return $data;
    }

    /**
     * Set option, used for simple select to set single option
     *
     * @param AbstractEntityAttributeOption $option
     */
    public function setOption(AbstractEntityAttributeOption $option)
    {
        $this->options->clear();
        $this->options[] = $option;
    }

    /**
     * Get related option, used for simple select to set single option
     *
     * @return AbstractEntityAttributeOption
     */
    public function getOption()
    {
        return $this->options->first();
    }

    /**
     * Add option, used for multi select to add many options
     *
     * @param AbstractEntityAttributeOption $option
     *
     * @return AbstractEntityFlexible
     */
    public function addOption(AbstractEntityAttributeOption $option)
    {
        $this->options[] = $option;

        return $this;
    }

    /**
     * Get options, used for multi select to retrieve many options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Get used currency
     * @return string $currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set used currency
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * Get used unit
     * @return string $unit
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * Set used unit
     * @param string $unit
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;
    }
}
