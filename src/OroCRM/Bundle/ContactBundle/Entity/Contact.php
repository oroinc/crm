<?php

namespace OroCRM\Bundle\ContactBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use OroCRM\Bundle\AccountBundle\Entity\Account;

use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Exclude;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\FlexibleEntityBundle\Entity\Mapping\AbstractEntityFlexible;

/**
 * @ORM\Entity(repositoryClass="Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository")
 * @ORM\Table(name="orocrm_contact")
 * @ORM\HasLifecycleCallbacks()
 */
class Contact extends AbstractEntityFlexible
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
     * @var \Oro\Bundle\FlexibleEntityBundle\Model\AbstractFlexibleValue[]
     * @ORM\OneToMany(targetEntity="OroCRM\Bundle\ContactBundle\Entity\Value\ContactValue", mappedBy="entity", cascade={"persist", "remove"},orphanRemoval=true)
     * @Exclude
     */
    protected $values;

    public function __construct()
    {
        parent::__construct();
        $this->groups   = new ArrayCollection();
        $this->accounts = new ArrayCollection();
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

    public function __toString()
    {
        try {
            $firstNameAttr = $this->getValue('first_name');
            $lastNameAttr = $this->getValue('last_name');

            $firstName = $firstNameAttr ? (string)$firstNameAttr->getData() : '';
            $lastName = $lastNameAttr ? (string)$lastNameAttr->getData(): '';
            return trim($firstName . ' ' . $lastName);
        } catch (\Exception $e) {
            return 'N/A';
        }
    }
}
