<?php
namespace Oro\Bundle\FlexibleEntityBundle\Entity\Mapping;

use Symfony\Component\HttpFoundation\File\File;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractFlexible;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractFlexibleValue;
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
abstract class AbstractEntityFlexibleValue extends AbstractFlexibleValue
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
     * @var date $datetime
     *
     * @ORM\Column(name="value_datetime", type="datetime", nullable=true)
     */
    protected $datetime;

    /**
     * Store options values
     * @var options ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AbstractEntityAttributeOption")
     * @ORM\JoinTable(name="oro_flexibleentity_values_options",
     *      joinColumns={@ORM\JoinColumn(name="value_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="option_id", referencedColumnName="id")}
     * )
     */
    protected $options;

    /**
     * Store upload values
     *
     * @var Media $media
     *
     * @ORM\OneToOne(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\Media", cascade="persist")
     * @ORM\JoinColumn(name="media_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $media;

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
        $name = 'set'.ucfirst($this->attribute->getBackendType());

        return $this->$name($data);
    }

    /**
     * Get data
     *
     * @return mixed
     */
    public function getData()
    {
        $name = 'get'.ucfirst($this->attribute->getBackendType());

        return $this->$name();
    }

    /**
     * Get varchar data
     *
     * @return string
     */
    public function getVarchar()
    {
        return $this->varchar;
    }

    /**
     * Set varchar data
     *
     * @param string $varchar
     *
     * @return EntityAttributeValue
     */
    public function setVarchar($varchar)
    {
        $this->varchar = $varchar;

        return $this;
    }

    /**
     * Get integer data
     *
     * @return integer
     */
    public function getInteger()
    {
        return $this->integer;
    }

    /**
     * Set integer data
     *
     * @param integer $integer
     *
     * @return EntityAttributeValue
     */
    public function setInteger($integer)
    {
        $this->integer = $integer;

        return $this;
    }

    /**
     * Get decimal data
     *
     * @return double
     */
    public function getDecimal()
    {
        return $this->decimal;
    }

    /**
     * Set decimal data
     *
     * @param double $decimal
     *
     * @return EntityAttributeValue
     */
    public function setDecimal($decimal)
    {
        $this->decimal = $decimal;

        return $this;
    }

    /**
     * Get text data
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set text data
     *
     * @param string $text
     *
     * @return EntityAttributeValue
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get date data
     *
     * @return date
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set date data
     *
     * @param date $date
     *
     * @return EntityAttributeValue
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get datetime data
     *
     * @return datetime
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * Set datetime data
     *
     * @param datetime $datetime
     *
     * @return EntityAttributeValue
     */
    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;

        return $this;
    }

    /**
     * Set option, used for simple select to set single option
     *
     * @param AbstractEntityAttributeOption $option
     *
     * @return AbstractEntityFlexibleValue
     */
    public function setOption(AbstractEntityAttributeOption $option)
    {
        $this->options->clear();
        $this->options[] = $option;

        return $this;
    }

    /**
     * Get related option, used for simple select to set single option
     *
     * @return AbstractEntityAttributeOption
     */
    public function getOption()
    {
        $option = $this->options->first();

        return ($option === false) ? null : $option;
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
     * Set options, used for multi select to retrieve many options
     *
     * @param ArrayCollection $options
     *
     * @return AbstractEntityFlexibleValue
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return ;
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

    /**
     * Get media
     *
     * @return \Oro\Bundle\FlexibleEntityBundle\Entity\Media
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * Set media
     *
     * @param \Oro\Bundle\FlexibleEntityBundle\Entity\Media $media
     *
     * @return \Oro\Bundle\FlexibleEntityBundle\Entity\ProductValue
     */
    public function setMedia($media)
    {
        $this->media = $media;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        $data = $this->getData();

        if ($data instanceof \DateTime) {
            $data = $data->format(\DateTime::ISO8601);
        }

        if ($data instanceof \Doctrine\Common\Collections\Collection) {
            $items = array();
            foreach ($data as $item) {
                $items[]= $item->__toString();
            }

            return implode(', ', $items);

        } else if (is_object($data)) {

            return $data->__toString();
        }

        return $data;
    }
}
