<?php

namespace Oro\Bundle\ContactBundle\Entity\Value;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexibleValue;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Oro\Bundle\ContactBundle\Entity\Contact;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * @ORM\Table(name="oro_contact_value")
 * @ORM\Entity
 * @Gedmo\Loggable(logEntryClass="Oro\Bundle\DataAuditBundle\Entity\Audit")
 */
class ContactValue extends AbstractEntityFlexibleValue
{
    /**
     * @var Attribute $attribute
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\Attribute")
     * @ORM\JoinColumn(name="attribute_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $attribute;

    /**
     * @var Contact $entity
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\ContactBundle\Entity\Contact", inversedBy="values")
     */
    protected $entity;

    /**
     * Custom backend type to store options and theirs values
     *
     * @var ArrayCollection $options
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\AttributeOption")
     * @ORM\JoinTable(
     *     name="oro_contact_value_option",
     *     joinColumns={@ORM\JoinColumn(name="value_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="option_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $options;

    /**
     * Store account
     *
     * @var Account $account
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\AccountBundle\Entity\Account", cascade="persist")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $account;

    /**
     * Store user
     *
     * @var User $user
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User", cascade="persist")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $user;

    /**
     * Store contact
     *
     * @var Contact $contact
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\ContactBundle\Entity\Contact", cascade="persist")
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $contact;

    /**
     * Store collection attributes relations
     *
     * @var ArrayCollection $collection
     *
     * @ORM\ManyToMany(targetEntity="Oro\Bundle\FlexibleEntityBundle\Entity\Collection",cascade={"persist"})
     * @ORM\JoinTable(
     *     name="oro_contact_collection_relation",
     *     joinColumns={@ORM\JoinColumn(name="value_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="collection_data_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $collection;

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
     * Get account
     *
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set account
     *
     * @param Account $account
     *
     * @return ContactValue
     */
    public function setAccount($account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user
     *
     * @param User $user
     *
     * @return ContactValue
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get contact
     *
     * @return Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set user
     *
     * @param Contact $contact
     *
     * @return ContactValue
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Store address values
     *
     * @var Address $media
     *
     * @ORM\OneToOne(targetEntity="Oro\Bundle\AddressBundle\Entity\Address", cascade="persist")
     * @ORM\JoinColumn(name="address_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $address;

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
     * @return ContactValue
     */
    public function setAddress(Address $address)
    {
        $this->address = $address;

        return $this;
    }
}
