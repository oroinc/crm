<?php

namespace Oro\Bundle\CaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\CommentBundle\Entity\BaseComment;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;

/**
 * Store case comment in database.
 *
 * @ORM\Entity()
 * @ORM\Table(name="orocrm_case_comment")
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *      defaultValues={
 *          "entity"={
 *              "icon"="fa-comments"
 *          },
 *          "ownership"={
 *              "owner_type"="USER",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="user_owner_id",
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"="",
 *              "category"="account_management"
 *          },
 *          "activity"={
 *              "immutable"=true
 *          }
 *      }
 * )
 */
class CaseComment extends BaseComment implements ExtendEntityInterface
{
    use ExtendEntityTrait;

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
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\ContactBundle\Entity\Contact", cascade={"persist"})
     * @ORM\JoinColumn(name="contact_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $contact;

    /**
     * @var string
     *
     * @ORM\Column(name="public", type="boolean", options={"default"=false})
     */
    protected $public = false;

    /**
     * @param CaseEntity|null $case
     * @return self
     */
    public function setCase($case)
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
     * @return self
     */
    public function setContact($contact)
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
     * @param boolean $public
     * @return self
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
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getId();
    }
}
