<?php

namespace OroCRM\Bundle\CaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use OroCRM\Bundle\CaseBundle\Model\ExtendCaseComment;

/**
 * @ORM\Entity()
 */
class CaseComment extends ExtendCaseComment
{
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
