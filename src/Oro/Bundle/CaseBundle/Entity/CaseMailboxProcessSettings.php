<?php

namespace Oro\Bundle\CaseBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Extend\Entity\Autocomplete\OroCaseBundle_Entity_CaseMailboxProcessSettings;
use Oro\Bundle\EmailBundle\Entity\MailboxProcessSettings;
use Oro\Bundle\EntityConfigBundle\Metadata\Attribute\Config;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
use Oro\Bundle\TagBundle\Entity\Taggable;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Store case mailbox settings in a database.
 *
 * @mixin OroCaseBundle_Entity_CaseMailboxProcessSettings
 */
#[ORM\Entity]
#[Config(mode: 'hidden')]
class CaseMailboxProcessSettings extends MailboxProcessSettings implements Taggable, ExtendEntityInterface
{
    use ExtendEntityTrait;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'case_owner_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?User $owner = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'case_assign_to_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?User $assignTo = null;

    #[ORM\ManyToOne(targetEntity: CasePriority::class)]
    #[ORM\JoinColumn(name: 'case_priority_name', referencedColumnName: 'name', onDelete: 'SET NULL')]
    protected ?CasePriority $priority = null;

    #[ORM\ManyToOne(targetEntity: CaseStatus::class)]
    #[ORM\JoinColumn(name: 'case_status_name', referencedColumnName: 'name', onDelete: 'SET NULL')]
    protected ?CaseStatus $status = null;

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

    #[\Override]
    public function getType()
    {
        return 'case';
    }

    /**
     * @return string
     */
    #[\Override]
    public function __toString()
    {
        return (string)$this->getId();
    }

    #[\Override]
    public function getTaggableId()
    {
        return $this->getId();
    }

    #[\Override]
    public function getTags()
    {
        $this->tags = $this->tags ?: new ArrayCollection();

        return $this->tags;
    }

    #[\Override]
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }
}
