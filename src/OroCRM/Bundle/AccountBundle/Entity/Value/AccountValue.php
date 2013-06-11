<?php

namespace OroCRM\Bundle\AccountBundle\Entity\Value;

use Gedmo\Mapping\Annotation as Gedmo;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexibleValue;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\AddressBundle\Entity\Address;

/**
 * @ORM\Table(name="orocrm_account_value")
 * @ORM\Entity
 * @Gedmo\Loggable(logEntryClass="Oro\Bundle\DataAuditBundle\Entity\Audit")
 */
class AccountValue extends AbstractEntityFlexibleValue
{
    /**
     * @var Attribute $attribute
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\Attribute")
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $attribute;

    /**
     * @var Account $entity
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\AccountBundle\Entity\Account", inversedBy="values")
     */
    protected $entity;

    /**
     * Custom backend type to store options and theirs values
     *
     * @var ArrayCollection $options
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption")
     * @ORM\JoinTable(
     *     name="orocrm_account_value_option",
     *     joinColumns={@ORM\JoinColumn(name="value_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="option_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $options;

    /**
     * Store varchar value
     *
     * @var string $varchar
     *
     * @ORM\Column(name="value_string", type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    protected $varchar;

    /**
     * Store int value
     *
     * @var integer $integer
     *
     * @ORM\Column(name="value_integer", type="integer", nullable=true)
     * @Gedmo\Versioned
     */
    protected $integer;

    /**
     * Store decimal value
     *
     * @var double $decimal
     *
     * @ORM\Column(name="value_decimal", type="decimal", nullable=true)
     * @Gedmo\Versioned
     */
    protected $decimal;

    /**
     * Store text value
     *
     * @var string $text
     *
     * @ORM\Column(name="value_text", type="text", nullable=true)
     * @Gedmo\Versioned
     */
    protected $text;

    /**
     * Store date value
     *
     * @var date $date
     *
     * @ORM\Column(name="value_date", type="date", nullable=true)
     * @Gedmo\Versioned
     */
    protected $date;

    /**
     * Store datetime value
     *
     * @var date $datetime
     *
     * @ORM\Column(name="value_datetime", type="datetime", nullable=true)
     * @Gedmo\Versioned
     */
    protected $datetime;

    /**
     * Store address
     *
     * @var Address $address
     *
     * @ORM\OneToOne(targetEntity="Oro\Bundle\AddressBundle\Entity\Address", cascade="persist")
     * @ORM\JoinColumn(name="address_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $address;

    /**
     * Store collection attributes relations
     *
     * @var ArrayCollection $collection
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\Collection",cascade={"persist"})
     * @ORM\JoinTable(
     *     name="orocrm_account_collection_relation",
     *     joinColumns={@ORM\JoinColumn(name="value_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="collection_data_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $collection;

    /**
     * Get address
     *
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set address
     *
     * @param Address $address
     *
     * @return AccountValue
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }
}
