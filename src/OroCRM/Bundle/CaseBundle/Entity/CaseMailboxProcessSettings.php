<?php

namespace OroCRM\Bundle\CaseBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\TagBundle\Entity\Taggable;

use OroCRM\Bundle\CaseBundle\Model\ExtendCaseMailboxProcessSettings;

/**
 * @ORM\Entity
 * @Config
 */
class CaseMailboxProcessSettings extends ExtendCaseMailboxProcessSettings implements Taggable
{
    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="case_owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="case_assign_to_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $assignTo;

    /**
     * @var CasePriority
     *
     * @ORM\ManyToOne(targetEntity="CasePriority")
     * @ORM\JoinColumn(name="case_priority_name", referencedColumnName="name", onDelete="SET NULL")
     */
    protected $priority;

    /**
     * @var CaseStatus
     *
     * @ORM\ManyToOne(targetEntity="CaseStatus")
     * @ORM\JoinColumn(name="case_status_name", referencedColumnName="name", onDelete="SET NULL")
     */
    protected $status;

    /** @var ArrayCollection $tags */
    protected $tags;

    /**
     * @return mixed
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param mixed $owner
     *
     * @return $this
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAssignTo()
    {
        return $this->assignTo;
    }

    /**
     * @param mixed $assignTo
     *
     * @return $this
     */
    public function setAssignTo($assignTo)
    {
        $this->assignTo = $assignTo;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param mixed $priority
     *
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return array
     */
    public function getTags()
    {
        $this->tags = $this->tags ?: new ArrayCollection();

        return $this->tags;
    }

    /**
     * @param array $tags
     *
     * @return $this
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
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
     * Returns type of process.
     *
     * @return string
     */
    public function getType()
    {
        return 'case';
    }
}
