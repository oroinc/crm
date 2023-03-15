<?php

namespace Oro\Bundle\CaseBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EmailBundle\Entity\MailboxProcessSettings;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\TagBundle\Entity\Taggable;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Store case mailbox settings in a database.
 *
 * @ORM\Entity
 * @Config(
 *      mode="hidden"
 * )
 */
class CaseMailboxProcessSettings extends MailboxProcessSettings implements Taggable, ExtendEntityInterface
{
    use ExtendEntityTrait;

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

    /**
     * @var Collection $tags
     */
    protected $tags;

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param User $owner
     *
     * @return $this
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return User
     */
    public function getAssignTo()
    {
        return $this->assignTo;
    }

    /**
     * @param User $assignTo
     *
     * @return $this
     */
    public function setAssignTo($assignTo)
    {
        $this->assignTo = $assignTo;

        return $this;
    }

    /**
     * @return CasePriority
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param CasePriority $priority
     *
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return CaseStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param CaseStatus $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return 'case';
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getTaggableId()
    {
        return $this->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function getTags()
    {
        $this->tags = $this->tags ?: new ArrayCollection();

        return $this->tags;
    }

    /**
     * {@inheritdoc}
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }
}
