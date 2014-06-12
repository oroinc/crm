<?php

namespace OroCRM\Bundle\CaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;

use Oro\Bundle\UserBundle\Entity\User;
use OroCRM\Bundle\ContactBundle\Entity\Contact;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="orocrm_case_comment"
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Oro\Loggable
 * @Config(
 *  defaultValues={
 *      "dataaudit"={"auditable"=true},
 *      "entity"={
 *          "icon"="icon-list-alt"
 *      },
 *      "ownership"={
 *          "owner_type"="USER",
 *          "owner_field_name"="owner",
 *          "owner_column_name"="owner_id"
 *      },
 *      "security"={
 *          "type"="ACL"
 *      }
 *  }
 * )
 */
class CaseComment
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="text")
     * @Oro\Versioned
     */
    protected $body;

    /**
     * @var string
     *
     * @ORM\Column(name="public", type="boolean", options={"default"=false})
     * @Oro\Versioned
     */
    protected $public = true;

    /**
     * @var CaseEntity
     *
     * @ORM\ManyToOne(targetEntity="CaseEntity", inversedBy="comments", cascade={"persist"})
     * @ORM\JoinColumn(name="case_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $case;

    /**
     * @var Contact
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\ContactBundle\Entity\Contact", cascade={"persist"})
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\Versioned
     */
    protected $contact;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="SET NULL")
     * @Oro\Versioned
     */
    protected $owner;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updatedAt;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $body
     * @return CaseComment
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param boolean $public
     * @return CaseComment
     */
    public function setPublic($public)
    {
        $this->public = (bool)$public;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isPublic()
    {
        return (bool)$this->public;
    }

    /**
     * @param CaseEntity|null $case
     * @return CaseComment
     */
    public function setCase(CaseEntity $case = null)
    {
        $this->case = $case;

        return $this;
    }

    /**
     * @return CaseEntity|null
     */
    public function getCase()
    {
        return $this->case;
    }

    /**
     * @param Contact|null $contact
     * @return CaseComment
     */
    public function setContact(Contact $contact = null)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * @return Contact|null
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param User $owner
     * @return CaseComment
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param \DateTime $createdAt
     * @return CaseComment
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $updatedAt
     * @return CaseComment
     */
    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt  = $this->createdAt ? $this->createdAt : new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }
}
