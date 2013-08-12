<?php

namespace OroCRM\Bundle\AccountBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Exclude;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use OroCRM\Bundle\ContactBundle\Entity\Contact;

use Oro\Bundle\TagBundle\Entity\Taggable;
use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible;
use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Configurable;

use Oro\Bundle\UserBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository")
 * @ORM\Table(name="orocrm_account")
 * @ORM\HasLifecycleCallbacks()
 * @Oro\Loggable
 * @Configurable(
 *  defaultValues={
 *      "entity"={"label"="Account", "plural_label"="Accounts"},
 *      "acl"={"owner_type"="USER"}
 *  }
 * )
 */
class Account extends AbstractEntityFlexible implements Taggable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Soap\ComplexType("int", nillable=true)
     * @Type("integer")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=true)
     * @Soap\ComplexType("string")
     * @Type("string")
     * @Oro\Versioned
     */
    protected $name;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $userOwner;

    /**
     * Contacts storage
     *
     * @var ArrayCollection $contacts
     *
     * @ORM\ManyToMany(targetEntity="OroCRM\Bundle\ContactBundle\Entity\Contact", inversedBy="accounts")
     * @ORM\JoinTable(name="orocrm_account_to_contact")
     *
     * @Exclude
     */
    protected $contacts;

    /**
     * @var \Oro\Bundle\FlexibleEntityBundle\Model\AbstractFlexibleValue[]
     *
     * @ORM\OneToMany(targetEntity="OroCRM\Bundle\AccountBundle\Entity\Value\AccountValue", mappedBy="entity", cascade={"persist", "remove"},orphanRemoval=true)
     * @Exclude
     */
    protected $values;

    /**
     * @var ArrayCollection $tags
     */
    protected $tags;

    public function __construct()
    {
        parent::__construct();
        $this->contacts = new ArrayCollection();
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
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set account name
     *
     * @param  string  $name New name
     * @return Account
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get account created date/time
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created;
    }

    /**
     * Get account last update date/time
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated;
    }

    /**
     * Get contacts collection
     *
     * @return Collection
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * Add specified contact
     *
     * @param Contact $contact
     * @return Account
     */
    public function addContact(Contact $contact)
    {
        if (!$this->getContacts()->contains($contact)) {
            $this->getContacts()->add($contact);
            $contact->addAccount($this);
        }

        return $this;
    }

    /**
     * Remove specified contact
     *
     * @param Contact $contact
     * @return Account
     */
    public function removeContact(Contact $contact)
    {
        if ($this->getContacts()->contains($contact)) {
            $this->getContacts()->removeElement($contact);
            $contact->removeAccount($this);
        }

        return $this;
    }

    public function __toString()
    {
        return (string) $this->getName();
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
     * {@inheritdoc}
     */
    public function getTaggableId()
    {
        return $this->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getTags()
    {
        $this->tags = $this->tags ?: new ArrayCollection();

        return $this->tags;
    }

    /**
     * {@inheritdoc}
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->userOwner;
    }

    /**
     * @param User $userOwner
     * @return Account
     */
    public function setOwner(User $userOwner)
    {
        $this->userOwner = $userOwner;

        return $this;
    }
}
