<?php

namespace OroCRM\Bundle\CaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use OroCRM\Bundle\CaseBundle\Model\ExtendCaseComment;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

/**
 * @ORM\Entity()
 * @Config(
 *      defaultValues={
 *          "entity"={
 *              "icon"="icon-comments"
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
 *              "group_name"=""
 *          },
 *          "activity"={
 *              "immutable"=true
 *          }
 *      }
 * )
 */
class CaseComment extends ExtendCaseComment
{
    /**
     * @var CaseEntity
     *
     * @ORM\ManyToOne(targetEntity="CaseEntity", inversedBy="comments", cascade={"persist"})
     * @ORM\JoinColumn(name="cs_case_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $case;

    /**
     * @var Contact
     *
     * @ORM\ManyToOne(targetEntity="OroCRM\Bundle\ContactBundle\Entity\Contact", cascade={"persist"})
     * @ORM\JoinColumn(name="cs_contact_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $contact;

    /**
     * @var string
     *
     * @ORM\Column(name="cs_public", type="boolean", options={"default"=false})
     */
    protected $public = false;

    /**
     * @param CaseEntity|null $case
     */
    public function setCase($case)
    {
        $this->case = $case;
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
     */
    public function setContact($contact)
    {
        $this->contact = $contact;
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
     */
    public function setPublic($public)
    {
        $this->public = (bool)$public;
    }

    /**
     * @return boolean
     */
    public function isPublic()
    {
        return (bool)$this->public;
    }
}
