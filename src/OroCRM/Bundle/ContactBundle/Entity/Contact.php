<?php

namespace OroCRM\Bundle\ContactBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Exclude;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use OroCRM\Bundle\AccountBundle\Entity\Account;

use Oro\Bundle\TagBundle\Entity\Taggable;
use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible;

/**
 * @ORM\Entity(repositoryClass="Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository")
 * @ORM\Table(name="orocrm_contact")
 * @ORM\HasLifecycleCallbacks()
 */
class Contact extends AbstractEntityFlexible implements Taggable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Soap\ComplexType("int", nillable=true)
     * @Type("integer")
     */
    protected $id;

    /**
     * @var Group[]
     *
     * @ORM\ManyToMany(targetEntity="Group")
     * @ORM\JoinTable(name="orocrm_contact_to_contact_group",
     *      joinColumns={@ORM\JoinColumn(name="contact_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="contact_group_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     * @Soap\ComplexType("int[]", nillable=true)
     * @Exclude
     */
    protected $groups;

    /**
     * Accounts storage
     *
     * @var ArrayCollection $accounts
     *
     * @ORM\ManyToMany(targetEntity="OroCRM\Bundle\AccountBundle\Entity\Account", mappedBy="contacts")
     * @ORM\JoinTable(name="orocrm_contact_to_account")
     * @Exclude
     */
    protected $accounts;

    /**
     * @var ArrayCollection $multiAddress
     * @ORM\OneToMany(targetEntity="ContactAddress", mappedBy="owner", cascade={"all"})
     * @ORM\OrderBy({"primary" = "DESC"})
     *
     * @Exclude
     */
    protected $multiAddress;

    /**
     * @var \Oro\Bundle\FlexibleEntityBundle\Model\AbstractFlexibleValue[]
     * @ORM\OneToMany(targetEntity="OroCRM\Bundle\ContactBundle\Entity\Value\ContactValue", mappedBy="entity", cascade={"persist", "remove"},orphanRemoval=true)
     * @Exclude
     */
    protected $values;

    /**
     * Set name formatting using "%first%" and "%last%" placeholders
     *
     * @var string
     *
     * @Exclude
     */
    protected $nameFormat;

    /**
     * @var ArrayCollection
     */
    private $tags;

    public function __construct()
    {
        parent::__construct();
        $this->groups   = new ArrayCollection();
        $this->accounts = new ArrayCollection();
        $this->multiAddress = new ArrayCollection();
    }

    /**
     * Returns the account unique id.
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get contact created date/time
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created;
    }

    /**
     * Get contact last update date/time
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated;
    }

    /**
     * Pre persist event listener
     *
     * @ORM\PrePersist
     */
    public function beforeSave()
    {
        $this->created = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Pre update event handler
     *
     * @ORM\PreUpdate
     */
    public function doPreUpdate()
    {
        $this->updated = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Gets the groups related to contact
     *
     * @return Collection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Add specified group
     *
     * @param Group $group
     * @return Contact
     */
    public function addGroup(Group $group)
    {
        if (!$this->getGroups()->contains($group)) {
            $this->getGroups()->add($group);
        }

        return $this;
    }

    /**
     * Remove specified group
     *
     * @param Group $group
     * @return Contact
     */
    public function removeGroup(Group $group)
    {
        if ($this->getGroups()->contains($group)) {
            $this->getGroups()->removeElement($group);
        }

        return $this;
    }

    /**
     * Get accounts collection
     *
     * @return Collection
     */
    public function getAccounts()
    {
        return $this->accounts;
    }

    /**
     * Add specified account
     *
     * @param Account $account
     * @return Contact
     */
    public function addAccount(Account $account)
    {
        if (!$this->getAccounts()->contains($account)) {
            $this->getAccounts()->add($account);
            $account->addContact($this);
        }

        return $this;
    }

    /**
     * Remove specified account
     *
     * @param Account $account
     * @return Contact
     */
    public function removeAccount(Account $account)
    {
        if ($this->getAccounts()->contains($account)) {
            $this->getAccounts()->removeElement($account);
            $account->removeContact($this);
        }

        return $this;
    }

    /**
     * Set addresses
     *
     * @param ContactAddress[] $addresses
     * @return Contact
     */
    public function setMultiAddress($addresses)
    {
        $this->multiAddress->clear();

        foreach ($addresses as $address) {
            $this->addMultiAddress($address);
        }

        return $this;
    }

    /**
     * Add address
     *
     * @param ContactAddress $address
     * @return Contact
     */
    public function addMultiAddress(ContactAddress $address)
    {
        if (!$this->multiAddress->contains($address)) {
            $this->multiAddress->add($address);
            $address->setOwner($this);
        }

        return $this;
    }

    /**
     * Remove address
     *
     * @param mixed $address
     * @return Contact
     */
    public function removeMultiAddress($address)
    {
        if ($this->multiAddress->contains($address)) {
            $this->multiAddress->removeElement($address);
        }

        return $this;
    }

    /**
     * Get addresses
     *
     * @return ContactAddress[]
     */
    public function getMultiAddress()
    {
        return $this->multiAddress;
    }

    /**
     * Get full name format. Defaults to "%first% %last%".
     *
     * @return string
     */
    public function getNameFormat()
    {
        return $this->nameFormat ?  $this->nameFormat : '%first% %last%';
    }

    /**
     * Set new format for a full name display. Use %first% and %last% placeholders, for example: "%last%, %first%".
     *
     * @param  string $format New format string
     * @return Contact
     */
    public function setNameFormat($format)
    {
        $this->nameFormat = $format;

        return $this;
    }

    /**
     * Return full contact name according to name format
     *
     * @see Contact::setNameFormat()
     * @param  string $format [optional]
     * @return string
     */
    public function getFullname($format = '')
    {
        return str_replace(
            array('%first%', '%last%'),
            array($this->getAttributeData('first_name'), $this->getAttributeData('last_name')),
            $format ? $format : $this->getNameFormat()
        );
    }

    public function __toString()
    {
        return trim($this->getAttributeData('first_name') . ' ' . $this->getAttributeData('last_name'));
    }

    /**
     * Get attribute value data by code
     *
     * @param $attributeCode
     * @return \Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface|string
     */
    public function getAttributeData($attributeCode)
    {
        try {
            $value = $this->getValue($attributeCode);
            if ($value) {
                $value = trim($value->getData());
            }
        } catch (\Exception $e) {
            $value = '';
        }

        return $value;
    }

    /**
     * Returns the unique taggable resource identifier
     *
     * @return string
     */
    public function getTaggableId()
    {
        return $this->getId();
    }

    /**
     * Returns the collection of tags for this Taggable entity
     *
     * @return ArrayCollection
     */
    public function getTags()
    {
        $this->tags = $this->tags ?: new ArrayCollection();

        return $this->tags;
    }
}
